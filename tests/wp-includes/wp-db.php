<?php

/**
 * Test WPDB methods
 *
 * @group wpdb
 */
class Tests_WP_DB extends WP_UnitTestCase {

	/**
	 * Query log
	 * @var array
	 */
	protected $_queries = array();

	/**
	 * Our special WPDB
	 * @var resource
	 */
	protected static $_wpdb;

	public static function setUpBeforeClass() {
		self::$_wpdb = new wpdb_exposed_methods_for_testing();
	}

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();
		$this->_queries = array();
		add_filter( 'query', array( $this, 'query_filter' ) );
	}

	/**
	 * Tear down the test fixture
	 */
	public function tearDown() {
		remove_filter( 'query', array( $this, 'query_filter' ) );
		parent::tearDown();
	}

	/**
	 * Log each query
	 * @param string $sql
	 * @return string
	 */
	public function query_filter( $sql ) {
		$this->_queries[] = $sql;
		return $sql;
	}

	/**
	 * Test that WPDB will reconnect when the DB link dies
	 *
	 * @covers wpdb::get_var
	 * @covers wpdb::check_connection
	 * @ticket 5932
	 */
	public function test_db_reconnect() {
		global $wpdb;

		$var = $wpdb->get_var( "SELECT ID FROM $wpdb->users LIMIT 1" );
		$this->assertGreaterThan( 0, $var );

		if ( $wpdb->use_mysqli ) {
			mysqli_close( $wpdb->dbh );
		} else {
			mysql_close( $wpdb->dbh );
		}
		unset( $wpdb->dbh );

		$var = $wpdb->get_var( "SELECT ID FROM $wpdb->users LIMIT 1" );
		$this->assertGreaterThan( 0, $var );
	}

	/**
	 * Test that floats formatted as "0,700" get sanitized properly by wpdb
	 * @global mixed $wpdb
	 *
	 * @covers wpdb::prepare
	 * @ticket 19861
	 */
	public function test_locale_floats() {
		global $wpdb;

		// Save the current locale settings
		$current_locales = explode( ';', setlocale( LC_ALL, 0 ) );

		// Switch to Russian
		$flag = setlocale( LC_ALL, 'ru_RU.utf8', 'rus', 'fr_FR.utf8', 'fr_FR', 'de_DE.utf8', 'de_DE', 'es_ES.utf8', 'es_ES' );
		if ( false === $flag )
			$this->markTestSkipped( 'No European languages available for testing' );

		// Try an update query
		$wpdb->suppress_errors( true );
		$wpdb->update(
			'test_table',
			array( 'float_column' => 0.7 ),
			array( 'meta_id' => 5 ),
			array( '%f' ),
			array( '%d' )
		);
		$wpdb->suppress_errors( false );

		// Ensure the float isn't 0,700
		$this->assertContains( '0.700', array_pop( $this->_queries ) );

		// Try a prepare
		$sql = $wpdb->prepare( "UPDATE test_table SET float_column = %f AND meta_id = %d", 0.7, 5 );
		$this->assertContains( '0.700', $sql );

		// Restore locale settings
		foreach ( $current_locales as $locale_setting ) {
			if ( false !== strpos( $locale_setting, '=' ) ) {
				list( $category, $locale ) = explode( '=', $locale_setting );
				if ( defined( $category ) )
					setlocale( constant( $category ), $locale );
			} else {
				setlocale( LC_ALL, $locale_setting );
			}
		}
	}

	/**
	 * @covers wpdb::esc_like
	 * @ticket 10041
	 */
	function test_esc_like() {
		global $wpdb;

		$inputs = array(
			'howdy%', //Single Percent
			'howdy_', //Single Underscore
			'howdy\\', //Single slash
			'howdy\\howdy%howdy_', //The works
			'howdy\'"[[]*#[^howdy]!+)(*&$#@!~|}{=--`/.,<>?', //Plain text
		);
		$expected = array(
			'howdy\\%',
			'howdy\\_',
			'howdy\\\\',
			'howdy\\\\howdy\\%howdy\\_',
			'howdy\'"[[]*#[^howdy]!+)(*&$#@!~|}{=--`/.,<>?',
		);

		foreach ($inputs as $key => $input) {
			$this->assertEquals($expected[$key], $wpdb->esc_like($input));
		}
	}

	/**
	 * Test LIKE Queries
	 *
	 * Make sure $wpdb is fully compatible with esc_like() by testing the identity of various strings.
	 * When escaped properly, a string literal is always LIKE itself (1)
	 * and never LIKE any other string literal (0) no matter how crazy the SQL looks.
	 *
	 * @covers wpdb::get_var
	 * @covers wpdb::prepare
	 * @ticket 10041
	 * @dataProvider data_like_query
	 * @param $data string The haystack, raw.
	 * @param $like string The like phrase, raw.
         * @param $result string The expected comparison result; '1' = true, '0' = false
	 */
	function test_like_query( $data, $like, $result ) {
		global $wpdb;
		return $this->assertEquals( $result, $wpdb->get_var( $wpdb->prepare( "SELECT %s LIKE %s", $data, $wpdb->esc_like( $like ) ) ) );
	}

	function data_like_query() {
		return array(
			array(
				'aaa',
				'aaa',
				'1',
			),
			array(
				'a\\aa', // SELECT 'a\\aa'  # This represents a\aa in both languages.
				'a\\aa', // LIKE 'a\\\\aa'
				'1',
			),
			array(
				'a%aa',
				'a%aa',
				'1',
			),
			array(
				'aaaa',
				'a%aa',
				'0',
			),
			array(
				'a\\%aa', // SELECT 'a\\%aa'
				'a\\%aa', // LIKE 'a\\\\\\%aa' # The PHP literal would be "LIKE 'a\\\\\\\\\\\\%aa'".  This is why we need reliable escape functions!
				'1',
			),
			array(
				'a%aa',
				'a\\%aa',
				'0',
			),
			array(
				'a\\%aa',
				'a%aa',
				'0',
			),
			array(
				'a_aa',
				'a_aa',
				'1',
			),
			array(
				'aaaa',
				'a_aa',
				'0',
			),
			array(
				'howdy\'"[[]*#[^howdy]!+)(*&$#@!~|}{=--`/.,<>?',
				'howdy\'"[[]*#[^howdy]!+)(*&$#@!~|}{=--`/.,<>?',
				'1',
			),
		);
	}

	/**
	 * @ticket 18510
	 */
	function test_wpdb_supposedly_protected_properties() {
		global $wpdb;

		$this->assertNotEmpty( $wpdb->dbh );
		$dbh = $wpdb->dbh;
		$this->assertNotEmpty( $dbh );
		$this->assertTrue( isset( $wpdb->dbh ) ); // Test __isset()
		unset( $wpdb->dbh );
		$this->assertTrue( empty( $wpdb->dbh ) );
		$wpdb->dbh = $dbh;
		$this->assertNotEmpty( $wpdb->dbh );
	}

	/**
	 * @ticket 21212
	 */
	function test_wpdb_actually_protected_properties() {
		global $wpdb;

		$new_meta = "HAHA I HOPE THIS DOESN'T WORK";

		$col_meta = $wpdb->col_meta;
		$wpdb->col_meta = $new_meta;

		$this->assertNotEquals( $col_meta, $new_meta );
		$this->assertEquals( $col_meta, $wpdb->col_meta );
	}

	/**
	 * @ticket 18510
	 */
	function test_wpdb_nonexistent_properties() {
		global $wpdb;

		$this->assertTrue( empty( $wpdb->nonexistent_property ) );
		$wpdb->nonexistent_property = true;
		$this->assertTrue( $wpdb->nonexistent_property );
		$this->assertTrue( isset( $wpdb->nonexistent_property ) );
		unset( $wpdb->nonexistent_property );
		$this->assertTrue( empty( $wpdb->nonexistent_property ) );
	}

	/**
	 * Test that an escaped %%f is not altered
	 *
	 * @covers wpdb::prepare
	 * @ticket 19861
	 */
	public function test_double_escaped_placeholders() {
		global $wpdb;
		$sql = $wpdb->prepare( "UPDATE test_table SET string_column = '%%f is a float, %%d is an int %d, %%s is a string', field = %s", 3, '4' );
		$this->assertEquals( "UPDATE test_table SET string_column = '%f is a float, %d is an int 3, %s is a string', field = '4'", $sql );
	}

	/**
	 * Test that SQL modes are set correctly
	 *
	 * @covers wpdb::get_var
	 * @covers wpdb::set_sql_mode
	 * @ticket 26847
	 */
	function test_set_sql_mode() {
		global $wpdb;

		$current_modes = $wpdb->get_var( 'SELECT @@SESSION.sql_mode;' );

		$new_modes = array( 'IGNORE_SPACE', 'NO_AUTO_CREATE_USER' );

		$wpdb->set_sql_mode( $new_modes );

		$check_new_modes = $wpdb->get_var( 'SELECT @@SESSION.sql_mode;' );
		$this->assertEqualSets( $new_modes, explode( ',', $check_new_modes ) );

		$wpdb->set_sql_mode( explode( ',', $current_modes ) );
	}

	/**
	 * Test that incompatible SQL modes are blocked
	 *
	 * @covers wpdb::get_var
	 * @covers wpdb::set_sql_mode
	 * @ticket 26847
	 */
	function test_set_incompatible_sql_mode() {
		global $wpdb;

		$current_modes = $wpdb->get_var( 'SELECT @@SESSION.sql_mode;' );

		$new_modes = array( 'IGNORE_SPACE', 'NO_ZERO_DATE', 'NO_AUTO_CREATE_USER' );
		$wpdb->set_sql_mode( $new_modes );
		$check_new_modes = $wpdb->get_var( 'SELECT @@SESSION.sql_mode;' );
		$this->assertNotContains( 'NO_ZERO_DATE', explode( ',', $check_new_modes ) );

		$wpdb->set_sql_mode( explode( ',', $current_modes ) );
	}

	/**
	 * Test that incompatible SQL modes can be changed
	 *
	 * @covers wpdb::get_var
	 * @covers wpdb::set_sql_mode
	 * @ticket 26847
	 */
	function test_set_allowed_incompatible_sql_mode() {
		global $wpdb;

		$current_modes = $wpdb->get_var( 'SELECT @@SESSION.sql_mode;' );

		$new_modes = array( 'IGNORE_SPACE', 'ONLY_FULL_GROUP_BY', 'NO_AUTO_CREATE_USER' );

		add_filter( 'incompatible_sql_modes', array( $this, 'filter_allowed_incompatible_sql_mode' ), 1, 1 );
		$wpdb->set_sql_mode( $new_modes );
		remove_filter( 'incompatible_sql_modes', array( $this, 'filter_allowed_incompatible_sql_mode' ), 1 );

		$check_new_modes = $wpdb->get_var( 'SELECT @@SESSION.sql_mode;' );
		$this->assertContains( 'ONLY_FULL_GROUP_BY', explode( ',', $check_new_modes ) );

		$wpdb->set_sql_mode( explode( ',', $current_modes ) );
	}

	public function filter_allowed_incompatible_sql_mode( $modes ) {
		$pos = array_search( 'ONLY_FULL_GROUP_BY', $modes );
		$this->assertGreaterThanOrEqual( 0, $pos );

		if ( FALSE === $pos ) {
			return $modes;
		}

		unset( $modes[ $pos ] );
		return $modes;
	}

	/**
	 * @covers wpdb::prepare
	 * @ticket 25604
	 * @expectedIncorrectUsage wpdb::prepare
	 */
	function test_prepare_without_arguments() {
		global $wpdb;
		$id = 0;
		// This, obviously, is an incorrect prepare.
		$prepared = $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE id = $id", $id );
		$this->assertEquals( "SELECT * FROM $wpdb->users WHERE id = 0", $prepared );
	}

	/**
	 * @covers wpdb::db_version
	 */
	function test_db_version() {
		global $wpdb;

		$this->assertTrue( version_compare( $wpdb->db_version(), '5.0', '>=' ) );
	}

	/**
	 * @covers wpdb::get_caller
	 */
	function test_get_caller() {
		global $wpdb;
		$str = $wpdb->get_caller();
		$calls = explode( ', ', $str );
		$called = join( '->', array( __CLASS__, __FUNCTION__ ) );
		$this->assertEquals( $called, end( $calls ) );
	}

	/**
	 * @covers wpdb::db_version
	 * @covers wpdb::has_cap
	 */
	function test_has_cap() {
		global $wpdb;
		$this->assertTrue( $wpdb->has_cap( 'collation' ) );
		$this->assertTrue( $wpdb->has_cap( 'group_concat' ) );
		$this->assertTrue( $wpdb->has_cap( 'subqueries' ) );
		$this->assertTrue( $wpdb->has_cap( 'COLLATION' ) );
		$this->assertTrue( $wpdb->has_cap( 'GROUP_CONCAT' ) );
		$this->assertTrue( $wpdb->has_cap( 'SUBQUERIES' ) );
		$this->assertEquals(
			version_compare( $wpdb->db_version(), '5.0.7', '>=' ),
			$wpdb->has_cap( 'set_charset' )
		);
		$this->assertEquals(
			version_compare( $wpdb->db_version(), '5.0.7', '>=' ),
			$wpdb->has_cap( 'SET_CHARSET' )
		);
	}

	/**
	 * @covers wpdb::supports_collation
	 * @expectedDeprecated supports_collation
	 */
	function test_supports_collation() {
		global $wpdb;
		$this->assertTrue( $wpdb->supports_collation() );
	}

	/**
	 * @covers wpdb::check_database_version
	 */
	function test_check_database_version() {
		global $wpdb;
		$this->assertEmpty( $wpdb->check_database_version() );
	}

	/**
	 * @covers wpdb::bail
	 * @expectedException WPDieException
	 */
	function test_bail() {
		global $wpdb;
		$wpdb->bail( 'Database is dead.' );
	}

	/**
	 * @covers wpdb::timer_start
	 * @covers wpdb::timer_stop
	 */
	function test_timers() {
		global $wpdb;

		$wpdb->timer_start();
		usleep( 5 );
		$stop = $wpdb->timer_stop();

		$this->assertNotEquals( $wpdb->time_start, $stop );
		$this->assertGreaterThan( $stop, $wpdb->time_start );
	}

	/**
	 * @covers wpdb::get_col_info
	 */
	function test_get_col_info() {
		global $wpdb;

		$wpdb->get_results( "SELECT ID FROM $wpdb->users" );

		$this->assertEquals( array( 'ID' ), $wpdb->get_col_info() );
		$this->assertEquals( array( $wpdb->users ), $wpdb->get_col_info( 'table' ) );
		$this->assertEquals( $wpdb->users, $wpdb->get_col_info( 'table', 0 ) );
	}

	/**
	 * @covers wpdb::query
	 * @covers wpdb::delete
	 */
	function test_query_and_delete() {
		global $wpdb;
		$rows = $wpdb->query( "INSERT INTO $wpdb->users (display_name) VALUES ('Walter Sobchak')" );
		$this->assertEquals( 1, $rows );
		$this->assertNotEmpty( $wpdb->insert_id );
		$d_rows = $wpdb->delete( $wpdb->users, array( 'ID' => $wpdb->insert_id ) );
		$this->assertEquals( 1, $d_rows );
	}

	/**
	 * @covers wpdb::query
	 * @covers wpdb::get_row
	 * @covers wpdb::prepare
	 */
	function test_get_row() {
		global $wpdb;
		$rows = $wpdb->query( "INSERT INTO $wpdb->users (display_name) VALUES ('Walter Sobchak')" );
		$this->assertEquals( 1, $rows );
		$this->assertNotEmpty( $wpdb->insert_id );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE ID = %d", $wpdb->insert_id ) );
		$this->assertInternalType( 'object', $row );
		$this->assertEquals( 'Walter Sobchak', $row->display_name );
	}

	/**
	 * @covers wpdb::insert
	 * @covers wpdb::replace
	 * @covers wpdb::get_row
	 * @covers wpdb::prepare
	 */
	function test_replace() {
		global $wpdb;
		$rows1 = $wpdb->insert( $wpdb->users, array( 'display_name' => 'Walter Sobchak' ) );
		$this->assertEquals( 1, $rows1 );
		$this->assertNotEmpty( $wpdb->insert_id );
		$last = $wpdb->insert_id;

		$rows2 = $wpdb->replace( $wpdb->users, array( 'ID' => $last, 'display_name' => 'Walter Replace Sobchak' ) );
		$this->assertEquals( 2, $rows2 );
		$this->assertNotEmpty( $wpdb->insert_id );

		$this->assertEquals( $last, $wpdb->insert_id );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE ID = %d", $last ) );
		$this->assertEquals( 'Walter Replace Sobchak', $row->display_name );
	}

	/**
	 * wpdb::update() requires a WHERE condition.
	 *
	 * @covers wpdb::suppress_errors
	 * @covers wpdb::update
	 * @ticket 26106
	 */
	function test_empty_where_on_update() {
		global $wpdb;
		$suppress = $wpdb->suppress_errors( true );
		$wpdb->update( $wpdb->posts, array( 'post_name' => 'burrito' ), array() );

		$expected1 = "UPDATE `{$wpdb->posts}` SET `post_name` = 'burrito' WHERE ";
		$this->assertNotEmpty( $wpdb->last_error );
		$this->assertEquals( $expected1, $wpdb->last_query );

		$wpdb->update( $wpdb->posts, array( 'post_name' => 'burrito' ), array( 'post_status' => 'taco' ) );

		$expected2 = "UPDATE `{$wpdb->posts}` SET `post_name` = 'burrito' WHERE `post_status` = 'taco'";
		$this->assertEmpty( $wpdb->last_error );
		$this->assertEquals( $expected2, $wpdb->last_query );
		$wpdb->suppress_errors( $suppress );
	}

	/**
	 * mysqli_ incorrect flush and further sync issues.
	 *
	 * @covers wpdb::suppress_errors
	 * @covers wpdb::query
	 * @covers wpdb::get_results
	 * @ticket 28155
	 */
	function test_mysqli_flush_sync() {
		global $wpdb;
		if ( ! $wpdb->use_mysqli ) {
			$this->markTestSkipped( 'mysqli not being used' );
		}

		$suppress = $wpdb->suppress_errors( true );

		$wpdb->query( 'DROP PROCEDURE IF EXISTS `test_mysqli_flush_sync_procedure`' );
		$wpdb->query( 'CREATE PROCEDURE `test_mysqli_flush_sync_procedure`() BEGIN
			SELECT ID FROM `' . $wpdb->posts . '` LIMIT 1;
		END' );

		if ( count( $wpdb->get_results( 'SHOW CREATE PROCEDURE `test_mysqli_flush_sync_procedure`' ) ) < 1 ) {
			$wpdb->suppress_errors( $suppress );
			$this->markTestSkipped( 'procedure could not be created (missing privileges?)' );
		}

		$post_id = $this->factory->post->create();

		$this->assertNotEmpty( $wpdb->get_results( 'CALL `test_mysqli_flush_sync_procedure`' ) );
		$this->assertNotEmpty( $wpdb->get_results( "SELECT ID FROM `{$wpdb->posts}` LIMIT 1" ) );

		// DROP PROCEDURE will cause a COMMIT, so we delete the post manually before that happens.
		wp_delete_post( $post_id, true );

		$wpdb->query( 'DROP PROCEDURE IF EXISTS `test_mysqli_flush_sync_procedure`' );
		$wpdb->suppress_errors( $suppress );
	}

	/**
	 * @ticket 21212
	 */
	function data_get_table_from_query() {
		$table = 'a_test_table_name';

		$queries = array(
			// Basic
			"SELECT * FROM $table",
			"SELECT * FROM `$table`",

			"INSERT $table",
			"INSERT IGNORE $table",
			"INSERT IGNORE INTO $table",
			"INSERT INTO $table",
			"INSERT LOW_PRIORITY $table",
			"INSERT DELAYED $table",
			"INSERT HIGH_PRIORITY $table",
			"INSERT LOW_PRIORITY IGNORE $table",
			"INSERT LOW_PRIORITY INTO $table",
			"INSERT LOW_PRIORITY IGNORE INTO $table",

			"REPLACE $table",
			"REPLACE INTO $table",
			"REPLACE LOW_PRIORITY $table",
			"REPLACE DELAYED $table",
			"REPLACE LOW_PRIORITY INTO $table",

			"UPDATE LOW_PRIORITY $table",
			"UPDATE LOW_PRIORITY IGNORE $table",

			"DELETE $table",
			"DELETE IGNORE $table",
			"DELETE IGNORE FROM $table",
			"DELETE FROM $table",
			"DELETE LOW_PRIORITY $table",
			"DELETE QUICK $table",
			"DELETE IGNORE $table",
			"DELETE LOW_PRIORITY FROM $table",

			// STATUS
			"SHOW TABLE STATUS LIKE '$table'",
			"SHOW TABLE STATUS WHERE NAME='$table'",

			"SHOW TABLES LIKE '$table'",
			"SHOW FULL TABLES LIKE '$table'",
			"SHOW TABLES WHERE NAME='$table'",

			// Extended
			"EXPLAIN SELECT * FROM $table",
			"EXPLAIN EXTENDED SELECT * FROM $table",
			"EXPLAIN EXTENDED SELECT * FROM `$table`",

			"DESCRIBE $table",
			"DESC $table",
			"EXPLAIN $table",
			"HANDLER $table",

			"LOCK TABLE $table",
			"LOCK TABLES $table",
			"UNLOCK TABLE $table",

			"RENAME TABLE $table",
			"OPTIMIZE TABLE $table",
			"BACKUP TABLE $table",
			"RESTORE TABLE $table",
			"CHECK TABLE $table",
			"CHECKSUM TABLE $table",
			"ANALYZE TABLE $table",
			"REPAIR TABLE $table",

			"TRUNCATE $table",
			"TRUNCATE TABLE $table",

			"CREATE TABLE $table",
			"CREATE TEMPORARY TABLE $table",
			"CREATE TABLE IF NOT EXISTS $table",

			"ALTER TABLE $table",
			"ALTER IGNORE TABLE $table",

			"DROP TABLE $table",
			"DROP TABLE IF EXISTS $table",

			"CREATE INDEX foo(bar(20)) ON $table",
			"CREATE UNIQUE INDEX foo(bar(20)) ON $table",
			"CREATE FULLTEXT INDEX foo(bar(20)) ON $table",
			"CREATE SPATIAL INDEX foo(bar(20)) ON $table",

			"DROP INDEX foo ON $table",

			"LOAD DATA INFILE 'wp.txt' INTO TABLE $table",
			"LOAD DATA LOW_PRIORITY INFILE 'wp.txt' INTO TABLE $table",
			"LOAD DATA CONCURRENT INFILE 'wp.txt' INTO TABLE $table",
			"LOAD DATA LOW_PRIORITY LOCAL INFILE 'wp.txt' INTO TABLE $table",
			"LOAD DATA INFILE 'wp.txt' REPLACE INTO TABLE $table",
			"LOAD DATA INFILE 'wp.txt' IGNORE INTO TABLE $table",

			"GRANT ALL ON TABLE $table",
			"REVOKE ALL ON TABLE $table",

			"SHOW COLUMNS FROM $table",
			"SHOW FULL COLUMNS FROM $table",
			"SHOW CREATE TABLE $table",
			"SHOW INDEX FROM $table",
		);

		foreach ( $queries as &$query ) {
			$query = array( $query, $table );
		}
		return $queries;
	}

	/**
	 * @covers wpdb::get_table_from_query
	 * @dataProvider data_get_table_from_query
	 * @ticket 21212
	 */
	function test_get_table_from_query( $query, $table ) {
		$this->assertEquals( $table, self::$_wpdb->get_table_from_query( $query ) );
	}

	function data_get_table_from_query_false() {
		$table = 'a_test_table_name';
		return array(
			array( "LOL THIS ISN'T EVEN A QUERY $table" ),
		);
	}

	/**
	 * @covers wpdb::get_table_from_query
	 * @dataProvider data_get_table_from_query_false
	 * @ticket 21212
	 */
	function test_get_table_from_query_false( $query ) {
		$this->assertFalse( self::$_wpdb->get_table_from_query( $query ) );
	}

	/**
	 * @ticket 21212
	 */
	function data_process_field_formats() {
		$core_db_fields_no_format_specified = array(
			array( 'post_content' => 'foo', 'post_parent' => 0 ),
			null,
			array(
				'post_content' => array( 'value' => 'foo', 'format' => '%s' ),
				'post_parent' => array( 'value' => 0, 'format' => '%d' ),
			)
		);

		$core_db_fields_formats_specified = array(
			array( 'post_content' => 'foo', 'post_parent' => 0 ),
			array( '%d', '%s' ), // These override core field_types
			array(
				'post_content' => array( 'value' => 'foo', 'format' => '%d' ),
				'post_parent' => array( 'value' => 0, 'format' => '%s' ),
			)
		);

		$misc_fields_no_format_specified = array(
			array( 'this_is_not_a_core_field' => 'foo', 'this_is_not_either' => 0 ),
			null,
			array(
				'this_is_not_a_core_field' => array( 'value' => 'foo', 'format' => '%s' ),
				'this_is_not_either' => array( 'value' => 0, 'format' => '%s' ),
			)
		);

		$misc_fields_formats_specified = array(
			array( 'this_is_not_a_core_field' => 0, 'this_is_not_either' => 1.2 ),
			array( '%d', '%f' ),
			array(
				'this_is_not_a_core_field' => array( 'value' => 0, 'format' => '%d' ),
				'this_is_not_either' => array( 'value' => 1.2, 'format' => '%f' ),
			)
		);

		$misc_fields_insufficient_formats_specified = array(
			array( 'this_is_not_a_core_field' => 0, 'this_is_not_either' => 's', 'nor_this' => 1 ),
			array( '%d', '%s' ), // The first format is used for the third
			array(
				'this_is_not_a_core_field' => array( 'value' => 0, 'format' => '%d' ),
				'this_is_not_either' => array( 'value' => 's', 'format' => '%s' ),
				'nor_this' => array( 'value' => 1, 'format' => '%d' ),
			)
		);

		$vars = get_defined_vars();
		// Push the variable name onto the end for assertSame $message
		foreach ( $vars as $var_name => $var ) {
			$vars[ $var_name ][] = $var_name;
		}
		return array_values( $vars );
	}

	/**
	 * @covers wpdb::process_field_formats
	 * @dataProvider data_process_field_formats
	 * @ticket 21212
	 */
	function test_process_field_formats( $data, $format, $expected, $message ) {
		$actual = self::$_wpdb->process_field_formats( $data, $format );
		$this->assertSame( $expected, $actual, $message );
	}

	/**
	 * @covers wpdb::process_fields
	 * @ticket 21212
	 */
	function test_process_fields() {
		global $wpdb;
		$data = array( 'post_content' => '¡foo foo foo!' );
		$expected = array(
			'post_content' => array(
				'value' => '¡foo foo foo!',
				'format' => '%s',
				'charset' => $wpdb->charset,
				'ascii' => false,
			)
		);

		$this->assertSame( $expected, self::$_wpdb->process_fields( $wpdb->posts, $data, null ) );
	}

	/**
	 * @covers wpdb::process_fields
	 * @ticket 21212
	 * @depends test_process_fields
	 */
	function test_process_fields_on_nonexistent_table( $data ) {
		self::$_wpdb->suppress_errors( true );
		$data = array( 'post_content' => '¡foo foo foo!' );
		$this->assertFalse( self::$_wpdb->process_fields( 'nonexistent_table', $data, null ) );
		self::$_wpdb->suppress_errors( false );
	}

	/**
	 * @covers wpdb::get_table_charset
	 * @ticket 21212
	 */
	function test_pre_get_table_charset_filter() {
		add_filter( 'pre_get_table_charset', array( $this, 'filter_pre_get_table_charset' ), 10, 2 );
		$charset = self::$_wpdb->get_table_charset( 'some_table' );
		remove_filter( 'pre_get_table_charset', array( $this, 'filter_pre_get_table_charset' ), 10 );

		$this->assertEquals( $charset, 'fake_charset' );
	}

	function filter_pre_get_table_charset( $charset, $table ) {
		return 'fake_charset';
	}

	/**
	 * @covers wpdb::get_col_charset
	 * @ticket 21212
	 */
	function test_pre_get_col_charset_filter() {
		add_filter( 'pre_get_col_charset', array( $this, 'filter_pre_get_col_charset' ), 10, 3 );
		$charset = self::$_wpdb->get_col_charset( 'some_table', 'some_col' );
		remove_filter( 'pre_get_col_charset', array( $this, 'filter_pre_get_col_charset' ), 10 );

		$this->assertEquals( $charset, 'fake_col_charset' );
	}

	function filter_pre_get_col_charset( $charset, $table, $column ) {
		return 'fake_col_charset';
	}

	/**
	 * @ticket 21212
	 */
	function data_strip_invalid_text() {
		$fields = array(
			'latin1' => array(
				// latin1. latin1 never changes.
				'charset'  => 'latin1',
				'value'    => "\xf0\x9f\x8e\xb7",
				'expected' => "\xf0\x9f\x8e\xb7"
			),
			'ascii' => array(
				// ascii gets special treatment, make sure it's covered
				'charset'  => 'ascii',
				'value'    => 'Hello World',
				'expected' => 'Hello World'
			),
			'utf8' => array(
				// utf8 only allows <= 3-byte chars
				'charset'  => 'utf8',
				'value'    => "H€llo\xf0\x9f\x98\x88World¢",
				'expected' => 'H€lloWorld¢'
			),
			'utf8mb3' => array(
				// utf8mb3 should behave the same an utf8
				'charset'  => 'utf8mb3',
				'value'    => "H€llo\xf0\x9f\x98\x88World¢",
				'expected' => 'H€lloWorld¢'
			),
			'utf8mb4' => array(
				// utf8mb4 allows 4-byte characters, too
				'charset'  => 'utf8mb4',
				'value'    => "H€llo\xf0\x9f\x98\x88World¢",
				'expected' => "H€llo\xf0\x9f\x98\x88World¢"
			),
			'koi8r' => array(
				// koi8r is a character set that needs to be checked in MySQL
				'charset'  => 'koi8r',
				'value'    => "\xfdord\xf2ress",
				'expected' => "\xfdord\xf2ress",
				'db'       => true
			),
			'hebrew' => array(
				// hebrew needs to be checked in MySQL, too
				'charset'  => 'hebrew',
				'value'    => "\xf9ord\xf7ress",
				'expected' => "\xf9ord\xf7ress",
				'db'       => true
			),
			'false' => array(
				// false is a column with no character set (ie, a number column)
				'charset'  => false,
				'value'    => 100,
				'expected' => 100
			),
		);

		if ( function_exists( 'mb_convert_encoding' ) ) {
			// big5 is a non-Unicode multibyte charset
			$utf8 = "a\xe5\x85\xb1b"; // UTF-8 Character 20849
			$big5 = mb_convert_encoding( $utf8, 'BIG-5', 'UTF-8' );
			$conv_utf8 = mb_convert_encoding( $big5, 'UTF-8', 'BIG-5' );
			// Make sure PHP's multibyte conversions are working correctly
			$this->assertNotEquals( $utf8, $big5 );
			$this->assertEquals( $utf8, $conv_utf8 );

			$fields['big5'] = array(
				'charset'  => 'big5',
				'value'    => $big5,
				'expected' => $big5
			);
		}

		// The data above is easy to edit. Now, prepare it for the data provider.
		$data_provider = $multiple = $multiple_expected = array();
		foreach ( $fields as $test_case => $field ) {
			$expected = $field;
			$expected['value'] = $expected['expected'];
			unset( $expected['expected'], $field['expected'] );

			// We're keeping track of these for our multiple-field test.
			$multiple[] = $field;
			$multiple_expected[] = $expected;

			// strip_invalid_text() expects an array of fields. We're testing one field at a time.
			$data = array( $field );
			$expected = array( $expected );

			// First argument is field data. Second is expected. Third is the message.
			$data_provider[] = array( $data, $expected, $test_case );
		}

		// Time for our test of multiple fields at once.
		$data_provider[] = array( $multiple, $multiple_expected, 'multiple fields/charsets' );

		return $data_provider;
	}

	/**
	 * @dataProvider data_strip_invalid_text
	 * @ticket 21212
	 */
	function test_strip_invalid_text( $data, $expected, $message ) {
		if ( $data[0]['charset'] === 'koi8r' ) {
			self::$_wpdb->query( 'SET NAMES koi8r' );
		}
		$actual = self::$_wpdb->strip_invalid_text( $data );
		$this->assertSame( $expected, $actual, $message );
	}

	/**
	 * @ ticket 21212
	 */
	function test_process_fields_failure() {
		global $wpdb;
		$data = array( 'post_content' => "H€llo\xf0\x9f\x98\x88World¢" );
		$this->assertFalse( self::$_wpdb->process_fields( $wpdb->posts, $data, null ) );
	}

	/**
	 * @ticket 21212
	 */
	function data_process_field_charsets() {
		$charset = $GLOBALS['wpdb']->charset; // This is how all tables were installed
		// 'value' and 'format' are $data, 'charset' ends up as part of $expected

		$no_string_fields = array(
			'post_parent' => array( 'value' => 10, 'format' => '%d', 'charset' => false ),
			'comment_count' => array( 'value' => 0, 'format' => '%d', 'charset' => false ),
		);

		$all_ascii_fields = array(
			'post_content' => array( 'value' => 'foo foo foo!', 'format' => '%s', 'charset' => false ),
			'post_excerpt' => array( 'value' => 'bar bar bar!', 'format' => '%s', 'charset' => false ),
		);

		// This is the same data used in process_field_charsets_for_nonexistent_table()
		$non_ascii_string_fields = array(
			'post_content' => array( 'value' => '¡foo foo foo!', 'format' => '%s', 'charset' => $charset, 'ascii' => false ),
			'post_excerpt' => array( 'value' => '¡bar bar bar!', 'format' => '%s', 'charset' => $charset, 'ascii' => false ),
		);

		$vars = get_defined_vars();
		unset( $vars['charset'] );
		foreach ( $vars as $var_name => $var ) {
			$data = $expected = $var;
			foreach ( $data as &$datum ) {
				// 'charset' and 'ascii' are part of the expected return only.
				unset( $datum['charset'], $datum['ascii'] );
			}

			$vars[ $var_name ] = array( $data, $expected, $var_name );
		}

		return array_values( $vars );
	}

	/**
	 * @dataProvider data_process_field_charsets
	 * @ticket 21212
	 */
	function test_process_field_charsets( $data, $expected, $message ) {
		$actual = self::$_wpdb->process_field_charsets( $data, $GLOBALS['wpdb']->posts );
		$this->assertSame( $expected, $actual, $message );
	}

	/**
	 * The test this test depends on first verifies that this
	 * would normally work against the posts table.
	 *
	 * @ticket 21212
	 * @depends test_process_field_charsets
	 */
	function test_process_field_charsets_on_nonexistent_table() {
		$data = array( 'post_content' => array( 'value' => '¡foo foo foo!', 'format' => '%s' ) );
		self::$_wpdb->suppress_errors( true );
		$this->assertFalse( self::$_wpdb->process_field_charsets( $data, 'nonexistent_table' ) );
		self::$_wpdb->suppress_errors( false );
	}

	/**
	 * @ticket 21212
	 */
	function test_check_ascii() {
		$ascii = "\0\t\n\r '" . '!"#$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
		$this->assertTrue( self::$_wpdb->check_ascii( $ascii ) );
	}

	/**
	 * @ticket 21212
	 */
	function test_check_ascii_false() {
		$this->assertFalse( self::$_wpdb->check_ascii( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ¡©«' ) );
	}

	/**
	 * @ticket 21212
	 */
	function test_strip_invalid_text_for_column() {
		global $wpdb;
		// Invalid 3-byte and 4-byte sequences
		$value = "H€llo\xe0\x80\x80World\xf0\xff\xff\xff¢";
		$expected = "H€lloWorld¢";
		$actual = $wpdb->strip_invalid_text_for_column( $wpdb->posts, 'post_content', $value );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Set of table definitions for testing wpdb::get_table_charset and wpdb::get_column_charset
	 * @var array
	 */
	protected $table_and_column_defs = array(
		array(
			'definition'      => '( a INT, b FLOAT )',
			'table_expected'  => false,
			'column_expected' => array( 'a' => false, 'b' => false )
		),
		array(
			'definition'      => '( a VARCHAR(50) CHARACTER SET big5, b TEXT CHARACTER SET big5 )',
			'table_expected'  => 'big5',
			'column_expected' => array( 'a' => 'big5', 'b' => 'big5' )
		),
		array(
			'definition'      => '( a VARCHAR(50) CHARACTER SET big5, b BINARY )',
			'table_expected'  => 'binary',
			'column_expected' => array( 'a' => 'big5', 'b' => false )
		),
		array(
			'definition'      => '( a VARCHAR(50) CHARACTER SET latin1, b BLOB )',
			'table_expected'  => 'binary',
			'column_expected' => array( 'a' => 'latin1', 'b' => false )
		),
		array(
			'definition'      => '( a VARCHAR(50) CHARACTER SET latin1, b TEXT CHARACTER SET koi8r )',
			'table_expected'  => 'koi8r',
			'column_expected' => array( 'a' => 'latin1', 'b' => 'koi8r' )
		),
		array(
			'definition'      => '( a VARCHAR(50) CHARACTER SET utf8mb3, b TEXT CHARACTER SET utf8mb3 )',
			'table_expected'  => 'utf8',
			'column_expected' => array( 'a' => 'utf8', 'b' => 'utf8' )
		),
		array(
			'definition'      => '( a VARCHAR(50) CHARACTER SET utf8, b TEXT CHARACTER SET utf8mb4 )',
			'table_expected'  => 'utf8',
			'column_expected' => array( 'a' => 'utf8', 'b' => 'utf8mb4' )
		),
		array(
			'definition'      => '( a VARCHAR(50) CHARACTER SET big5, b TEXT CHARACTER SET koi8r )',
			'table_expected'  => 'ascii',
			'column_expected' => array( 'a' => 'big5', 'b' => 'koi8r' )
		),
	);

	/**
	 * @ticket 21212
	 */
	function data_test_get_table_charset() {
		$table_name = 'test_get_table_charset';

		$vars = array();
		foreach( $this->table_and_column_defs as $value ) {
			$this_table_name = $table_name . '_' . rand_str( 5 );
			$drop = "DROP TABLE IF EXISTS $this_table_name";
			$create = "CREATE TABLE $this_table_name {$value['definition']}";
			$vars[] = array( $drop, $create, $this_table_name, $value['table_expected'] );
		}

		return $vars;
	}

	/**
	 * @dataProvider data_test_get_table_charset
	 * @ticket 21212
	 */
	function test_get_table_charset( $drop, $create, $table, $expected_charset ) {
		self::$_wpdb->query( $drop );

		if ( ! self::$_wpdb->has_cap( 'utf8mb4' ) && preg_match( '/utf8mb[34]/i', $create ) ) {
			$this->markTestSkipped( "This version of MySQL doesn't support utf8mb4." );
			return;
		}

		self::$_wpdb->query( $create );

		$charset = self::$_wpdb->get_table_charset( $table );
		$this->assertEquals( $charset, $expected_charset );

		$charset = self::$_wpdb->get_table_charset( strtoupper( $table ) );
		$this->assertEquals( $charset, $expected_charset );

		self::$_wpdb->query( $drop );
	}

	/**
	 * @ticket 21212
	 */
	function data_test_get_column_charset() {
		$table_name = 'test_get_column_charset';

		$vars = array();
		foreach( $this->table_and_column_defs as $value ) {
			$this_table_name = $table_name . '_' . rand_str( 5 );
			$drop = "DROP TABLE IF EXISTS $this_table_name";
			$create = "CREATE TABLE $this_table_name {$value['definition']}";
			$vars[] = array( $drop, $create, $this_table_name, $value['column_expected'] );
		}

		return $vars;
	}

	/**
	 * @dataProvider data_test_get_column_charset
	 * @ticket 21212
	 */
	function test_get_column_charset( $drop, $create, $table, $expected_charset ) {
		self::$_wpdb->query( $drop );

		if ( ! self::$_wpdb->has_cap( 'utf8mb4' ) && preg_match( '/utf8mb[34]/i', $create ) ) {
			$this->markTestSkipped( "This version of MySQL doesn't support utf8mb4." );
			return;
		}

		self::$_wpdb->query( $create );

		foreach ( $expected_charset as $column => $charset ) {
			$this->assertEquals( $charset, self::$_wpdb->get_col_charset( $table, $column ) );
			$this->assertEquals( $charset, self::$_wpdb->get_col_charset( strtoupper( $table ), strtoupper( $column ) ) );
		}

		self::$_wpdb->query( $drop );
	}

	/**
	 * @dataProvider data_test_get_column_charset
	 * @ticket 21212
	 */
	function test_get_column_charset_non_mysql( $drop, $create, $table, $columns ) {
		self::$_wpdb->query( $drop );

		if ( ! self::$_wpdb->has_cap( 'utf8mb4' ) && preg_match( '/utf8mb[34]/i', $create ) ) {
			$this->markTestSkipped( "This version of MySQL doesn't support utf8mb4." );
			return;
		}

		self::$_wpdb->is_mysql = false;

		self::$_wpdb->query( $create );

		$columns = array_keys( $columns );
		foreach ( $columns as $column => $charset ) {
			$this->assertEquals( false, self::$_wpdb->get_col_charset( $table, $column ) );
		}

		self::$_wpdb->query( $drop );

		self::$_wpdb->is_mysql = true;
	}

	/**
	 * @ticket 21212
	 */
	function data_strip_invalid_text_from_query() {
		$table_name = 'strip_invalid_text_from_query_table';
		$data = array(
			array(
				// binary tables don't get stripped
				"( a VARCHAR(50) CHARACTER SET utf8, b BINARY )", // create
				"('foo\xf0\x9f\x98\x88bar', 'foo')",              // query
				"('foo\xf0\x9f\x98\x88bar', 'foo')"               // expected result
			),
			array(
				// utf8/utf8mb4 tables default to utf8
				"( a VARCHAR(50) CHARACTER SET utf8, b VARCHAR(50) CHARACTER SET utf8mb4 )",
				"('foo\xf0\x9f\x98\x88bar', 'foo')",
				"('foobar', 'foo')"
			),
		);

		foreach( $data as &$value ) {
			$this_table_name = $table_name . '_' . rand_str( 5 );

			$value[0] = "CREATE TABLE $this_table_name {$value[0]}";
			$value[1] = "INSERT INTO $this_table_name VALUES {$value[1]}";
			$value[2] = "INSERT INTO $this_table_name VALUES {$value[2]}";
			$value[3] = "DROP TABLE IF EXISTS $this_table_name";
		}
		unset( $value );

		return $data;
	}

	/**
	 * @dataProvider data_strip_invalid_text_from_query
	 * @ticket 21212
	 */
	function test_strip_invalid_text_from_query( $create, $query, $expected, $drop ) {
		self::$_wpdb->query( $drop );

		if ( ! self::$_wpdb->has_cap( 'utf8mb4' ) && preg_match( '/utf8mb[34]/i', $create ) ) {
			$this->markTestSkipped( "This version of MySQL doesn't support utf8mb4." );
			return;
		}

		self::$_wpdb->query( $create );

		$return = self::$_wpdb->strip_invalid_text_from_query( $query );
		$this->assertEquals( $expected, $return );

		self::$_wpdb->query( $drop );
	}

	/**
	 * @ticket 21212
	 */
	function test_invalid_characters_in_query() {
		global $wpdb;
		$this->assertFalse( $wpdb->query( "INSERT INTO {$wpdb->posts} (post_content) VALUES ('foo\xf0\x9f\x98\x88bar')" ) );
	}

}
