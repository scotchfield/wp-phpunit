<?php

/**
 * @group pluggable
 */
class Tests_Pluggable extends WP_UnitTestCase {
	var $user_id;

	function setUp() {
		parent::setUp();
		$this->user_id = $this->factory->user->create();

		$_SERVER['REQUEST_METHOD'] = null;

		$_SERVER['SERVER_NAME'] = 'example.com';
		unset( $GLOBALS['phpmailer']->mock_sent );
	}

	function tearDown() {
		parent::tearDown();
		unset( $_SERVER['SERVER_NAME'] );
	}

	/**
	 * @covers ::wp_generate_auth_cookie
	 * @covers ::wp_validate_auth_cookie
	 * @group auth
	 */
	function test_auth_cookie_valid() {
		$cookie = wp_generate_auth_cookie( $this->user_id, time() + 3600, 'auth' );
		$this->assertEquals( $this->user_id, wp_validate_auth_cookie( $cookie, 'auth' ) );
	}

	/**
	 * @covers ::wp_generate_auth_cookie
	 * @covers ::wp_validate_auth_cookie
	 * @group auth
	 */
	function test_auth_cookie_invalid() {
		// 3600 or less and +3600 may occur in wp_validate_auth_cookie(),
		// as an ajax test may have defined DOING_AJAX, failing the test.

		$cookie = wp_generate_auth_cookie( $this->user_id, time() - 7200, 'auth' );
		$this->assertEquals( false, wp_validate_auth_cookie( $cookie, 'auth' ), 'expired cookie' );

		$cookie = wp_generate_auth_cookie( $this->user_id, time() + 3600, 'auth' );
		$this->assertEquals( false, wp_validate_auth_cookie( $cookie, 'logged_in' ), 'wrong auth scheme' );

		$cookie = wp_generate_auth_cookie( $this->user_id, time() + 3600, 'auth' );
		list($a, $b, $c) = explode('|', $cookie);
		$cookie = $a . '|' . ($b + 1) . '|' . $c;
		$this->assertEquals( false, wp_validate_auth_cookie( $this->user_id, 'auth' ), 'altered cookie' );
	}

	/**
	 * @covers ::wp_generate_auth_cookie
	 * @covers ::wp_validate_auth_cookie
	 * @group auth
	 */
	function test_auth_cookie_scheme() {
		// arbitrary scheme name
		$cookie = wp_generate_auth_cookie( $this->user_id, time() + 3600, 'foo' );
		$this->assertEquals( $this->user_id, wp_validate_auth_cookie( $cookie, 'foo' ) );

		// wrong scheme name - should fail
		$cookie = wp_generate_auth_cookie( $this->user_id, time() + 3600, 'foo' );
		$this->assertEquals( false, wp_validate_auth_cookie( $cookie, 'bar' ) );
	}

	/**
	 * @covers ::wp_set_password
	 * @covers ::wp_authenticate
	 * @group auth
	 * @ticket 23494
	 */
	function test_password_trimming() {
		$another_user = $this->factory->user->create( array( 'user_login' => 'password-triming-tests' ) );

		$passwords_to_test = array(
			'a password with no trailing or leading spaces',
			'a password with trailing spaces ',
			' a password with leading spaces',
			' a password with trailing and leading spaces ',
		);

		foreach( $passwords_to_test as $password_to_test ) {
			wp_set_password( $password_to_test, $another_user );
			$authed_user = wp_authenticate( 'password-triming-tests', $password_to_test );

			$this->assertInstanceOf( 'WP_User', $authed_user );
			$this->assertEquals( $another_user, $authed_user->ID );
		}
	}

	/**
	 * Test wp_hash_password trims whitespace
	 *
	 * This is similar to test_password_trimming but tests the "lower level"
	 * wp_hash_password function
	 *
	 * @covers ::wp_check_password
	 * @covers ::wp_hash_password
	 * @group auth
	 * @ticket 24973
	 */
	function test_wp_hash_password_trimming() {

		$password = ' pass with leading whitespace';
		$this->assertTrue( wp_check_password( 'pass with leading whitespace', wp_hash_password( $password ) ) );

		$password = 'pass with trailing whitespace ';
		$this->assertTrue( wp_check_password( 'pass with trailing whitespace', wp_hash_password( $password ) ) );

		$password = ' pass with whitespace ';
		$this->assertTrue( wp_check_password( 'pass with whitespace', wp_hash_password( $password ) ) );

		$password = "pass with new line \n";
		$this->assertTrue( wp_check_password( 'pass with new line', wp_hash_password( $password ) ) );

		$password = "pass with vertial tab o_O\x0B";
		$this->assertTrue( wp_check_password( 'pass with vertial tab o_O', wp_hash_password( $password ) ) );
	}

	/**
	 * @covers ::wp_verify_nonce
	 * @group auth
	 * @ticket 29217
	 */
	function test_wp_verify_nonce_with_empty_arg() {
		$this->assertFalse( wp_verify_nonce( '' ) );
		$this->assertFalse( wp_verify_nonce( null ) );
	}

	/**
	 * @covers ::wp_verify_nonce
	 * @group auth
	 * @ticket 29542
	 */
	function test_wp_verify_nonce_with_integer_arg() {
		$this->assertFalse( wp_verify_nonce( 1 ) );
	}

	/**
	 * @covers ::wp_set_password
	 * @covers ::get_user_by
	 * @covers ::wp_authenticate
	 * @group auth
	 */
	function test_password_length_limit() {
		$passwords = array(
			str_repeat( 'a', 4095 ), // short
			str_repeat( 'a', 4096 ), // limit
			str_repeat( 'a', 4097 ), // long
		);

		$user_id = $this->factory->user->create( array( 'user_login' => 'password-length-test' ) );

		wp_set_password( $passwords[1], $user_id );
		$user = get_user_by( 'id', $user_id );
		// phpass hashed password
		$this->assertStringStartsWith( '$P$', $user->data->user_pass );

		$user = wp_authenticate( 'password-length-test', $passwords[0] );
		// Wrong Password
		$this->assertInstanceOf( 'WP_Error', $user );

		$user = wp_authenticate( 'password-length-test', $passwords[1] );
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertEquals( $user_id, $user->ID );

		$user = wp_authenticate( 'password-length-test', $passwords[2] );
		// Wrong Password
		$this->assertInstanceOf( 'WP_Error', $user );


		wp_set_password( $passwords[2], $user_id );
		$user = get_user_by( 'id', $user_id );
		// Password broken by setting it to be too long.
		$this->assertEquals( '*', $user->data->user_pass );

		$user = wp_authenticate( 'password-length-test', '*' );
		$this->assertInstanceOf( 'WP_Error', $user );

		$user = wp_authenticate( 'password-length-test', '*0' );
		$this->assertInstanceOf( 'WP_Error', $user );

		$user = wp_authenticate( 'password-length-test', '*1' );
		$this->assertInstanceOf( 'WP_Error', $user );

		$user = wp_authenticate( 'password-length-test', $passwords[0] );
		// Wrong Password
		$this->assertInstanceOf( 'WP_Error', $user );

		$user = wp_authenticate( 'password-length-test', $passwords[1] );
		// Wrong Password
		$this->assertInstanceOf( 'WP_Error', $user );

		$user = wp_authenticate( 'password-length-test', $passwords[2] );
		// Password broken by setting it to be too long.
		$this->assertInstanceOf( 'WP_Error', $user );
	}

	/**
	 * @covers ::wp_mail
	 * @group mail
	 */
	function test_wp_mail_custom_boundaries() {
		$to = 'user@example.com';
		$subject = 'Test email with custom boundaries';
		$headers  = '' . "\n";
		$headers .= 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-Type: multipart/mixed; boundary="----=_Part_4892_25692638.1192452070893"' . "\n";
		$headers .= "\n";
		$body  = "\n";
		$body .= '------=_Part_4892_25692638.1192452070893' . "\n";
		$body .= 'Content-Type: text/plain; charset=ISO-8859-1' . "\n";
		$body .= 'Content-Transfer-Encoding: 7bit' . "\n";
		$body .= 'Content-Disposition: inline' . "\n";
		$body .= "\n";
		$body .= 'Here is a message with an attachment of a binary file.' . "\n";
		$body .= "\n";
		$body .= '------=_Part_4892_25692638.1192452070893' . "\n";
		$body .= 'Content-Type: image/x-icon; name=favicon.ico' . "\n";
		$body .= 'Content-Transfer-Encoding: base64' . "\n";
		$body .= 'Content-Disposition: attachment; filename=favicon.ico' . "\n";
		$body .= "\n";
		$body .= 'AAABAAEAEBAAAAAAAABoBQAAFgAAACgAAAAQAAAAIAAAAAEACAAAAAAAAAAAAAAAAAAAAAAAAAAA' . "\n";
		$body .= 'AAAAAAAAAAAAAACAAACAAAAAgIAAgAAAAIAAgACAgAAAwMDAAICAgAAAAP8AAP8AAAD//wD/AAAA' . "\n";
		$body .= '/wD/AP//AAD///8A//3/AP39/wD6/f8A+P3/AP/8/wD9/P8A+vz/AP/7/wD/+v8A/vr/APz6/wD4' . "\n";
		$body .= '+v8A+/n/APP5/wD/+P8A+vj/AO/4/wDm+P8A2fj/AP/3/wD/9v8A9vb/AP/1/wD69f8A9PT/AO30' . "\n";
		$body .= '/wD/8/8A//L/APnx/wD28P8A///+APj//gD2//4A9P/+AOP//gD//f4A6f/9AP///AD2//wA8//8' . "\n";
		$body .= 'APf9/AD///sA/v/7AOD/+wD/+vsA9/X7APr/+gDv/voA///5AP/9+QD/+/kA+e35AP//+ADm//gA' . "\n";
		$body .= '4f/4AP/9+AD0+/gA///3APv/9wDz//cA8f/3AO3/9wD/8fcA//32AP369gDr+vYA8f/1AOv/9QD/' . "\n";
		$body .= '+/UA///0APP/9ADq//QA///zAP/18wD///IA/fzyAP//8QD///AA9//wAPjw8AD//+8A8//vAP//' . "\n";
		$body .= '7gD9/+4A9v/uAP/u7gD//+0A9v/tAP7/6wD/+eoA///pAP//6AD2/+gA//nnAP/45wD38eYA/fbl' . "\n";
		$body .= 'AP/25AD29uQA7N/hAPzm4AD/690AEhjdAAAa3AAaJdsA//LXAC8g1gANH9YA+dnTAP/n0gDh5dIA' . "\n";
		$body .= 'DyjSABkk0gAdH9EABxDRAP/l0AAAJs4AGRTOAPPczQAAKs0AIi7MAA4UywD56soA8tPKANTSygD/' . "\n";
		$body .= '18kA6NLHAAAjxwDj28QA/s7CAP/1wQDw3r8A/9e8APrSrwDCtqoAzamjANmPiQDQj4YA35mBAOme' . "\n";
		$body .= 'fgDHj3wA1qR6AO+sbwDpmm8A2IVlAKmEYgCvaFoAvHNXAEq2VgA5s1UAPbhQAFWtTwBStU0ARbNN' . "\n";
		$body .= 'AEGxTQA7tEwAObZIAEq5RwDKdEYAULhDANtuQgBEtTwA1ls3ALhgMQCxNzEA2FsvAEC3LQB0MCkA' . "\n";
		$body .= 'iyYoANZTJwDLWyYAtjMlALE6JACZNSMAuW4iANlgIgDoWCEAylwgAMUuIAD3Vh8A52gdALRCHQCx' . "\n";
		$body .= 'WhwAsEkcALU4HACMOBwA0V4bAMYyGgCPJRoA218ZAJM7FwC/PxYA0msVAM9jFQD2XBUAqioVAIAf' . "\n";
		$body .= 'FQDhYRQAujMTAMUxEwCgLBMAnxIPAMsqDgCkFgsA6GMHALE2BAC9JQAAliIAAFYTAAAAAAAAAAAA' . "\n";
		$body .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' . "\n";
		$body .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/' . "\n";
		$body .= '//8AsbGxsbGxsbGxsbGxsbGxd7IrMg8PDw8PDw8PUBQeJXjQYE9PcKPM2NfP2sWhcg+BzTE7dLjb' . "\n";
		$body .= 'mG03YWaV4JYye8MPbsLZlEouKRRCg9SXMoW/U53enGRAFzCRtNO7mTiAyliw30gRTg9VbJCKfYs0' . "\n";
		$body .= 'j9VmuscfLTFbIy8SOhA0Inq5Y77GNBMYIxQUJzM2Vxx2wEmfyCYWMRldXCg5MU0aicRUms58SUVe' . "\n";
		$body .= 'RkwjPBRSNIfBMkSgvWkyPxVHFIaMSx1/0S9nkq7WdWo1a43Jt2UqgtJERGJ5m6K8y92znpNWIYS1' . "\n";
		$body .= 'UQ89Mmg5cXNaX0EkGyyI3KSsp6mvpaqosaatq7axsQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' . "\n";
		$body .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=' . "\n";
		$body .= '------=_Part_4892_25692638.1192452070893--' . "\n";
		$body .= "\n";

		wp_mail($to, $subject, $body, $headers);

		// We need some better assertions here but these catch the failure for now.
		$this->assertEquals($body, $GLOBALS['phpmailer']->mock_sent[0]['body']);
		$this->assertTrue(strpos($GLOBALS['phpmailer']->mock_sent[0]['header'], 'boundary="----=_Part_4892_25692638.1192452070893"') > 0);
		$this->assertTrue(strpos($GLOBALS['phpmailer']->mock_sent[0]['header'], 'charset=') > 0);
	}

	/**
	 * @covers ::wp_mail
	 * @group mail
	 * @ticket 17305
	 */
	function test_wp_mail_rfc2822_addresses() {
		$to = "Name <address@tld.com>";
		$from = "Another Name <another_address@different-tld.com>";
		$cc = "The Carbon Guy <cc@cc.com>";
		$bcc = "The Blind Carbon Guy <bcc@bcc.com>";
		$subject = "RFC2822 Testing";
		$message = "My RFC822 Test Message";
		$headers[] = "From: {$from}";
		$headers[] = "CC: {$cc}";
		$headers[] = "BCC: {$bcc}";

		wp_mail( $to, $subject, $message, $headers );

		// WordPress 3.2 and later correctly split the address into the two parts and send them seperately to PHPMailer
		// Earlier versions of PHPMailer were not touchy about the formatting of these arguments.
		$this->assertEquals('address@tld.com', $GLOBALS['phpmailer']->mock_sent[0]['to'][0][0]);
		$this->assertEquals('Name', $GLOBALS['phpmailer']->mock_sent[0]['to'][0][1]);
		$this->assertEquals('cc@cc.com', $GLOBALS['phpmailer']->mock_sent[0]['cc'][0][0]);
		$this->assertEquals('The Carbon Guy', $GLOBALS['phpmailer']->mock_sent[0]['cc'][0][1]);
		$this->assertEquals('bcc@bcc.com', $GLOBALS['phpmailer']->mock_sent[0]['bcc'][0][0]);
		$this->assertEquals('The Blind Carbon Guy', $GLOBALS['phpmailer']->mock_sent[0]['bcc'][0][1]);
		$this->assertEquals($message . "\n", $GLOBALS['phpmailer']->mock_sent[0]['body']);
	}

	/**
	 * @covers ::wp_mail
	 * @group mail
	 * @ticket 17305
	 */
	function test_wp_mail_multiple_rfc2822_to_addresses() {
		$to = "Name <address@tld.com>, Another Name <another_address@different-tld.com>";
		$subject = "RFC2822 Testing";
		$message = "My RFC822 Test Message";

		wp_mail( $to, $subject, $message );

		// WordPress 3.2 and later correctly split the address into the two parts and send them seperately to PHPMailer
		// Earlier versions of PHPMailer were not touchy about the formatting of these arguments.
		$this->assertEquals('address@tld.com', $GLOBALS['phpmailer']->mock_sent[0]['to'][0][0]);
		$this->assertEquals('Name', $GLOBALS['phpmailer']->mock_sent[0]['to'][0][1]);
		$this->assertEquals('another_address@different-tld.com', $GLOBALS['phpmailer']->mock_sent[0]['to'][1][0]);
		$this->assertEquals('Another Name', $GLOBALS['phpmailer']->mock_sent[0]['to'][1][1]);
		$this->assertEquals($message . "\n", $GLOBALS['phpmailer']->mock_sent[0]['body']);
	}

	/**
	 * @covers ::wp_mail
	 * @group mail
	 */
	function test_wp_mail_multiple_to_addresses() {
		$to = "address@tld.com, another_address@different-tld.com";
		$subject = "RFC2822 Testing";
		$message = "My RFC822 Test Message";

		wp_mail( $to, $subject, $message );

		$this->assertEquals('address@tld.com', $GLOBALS['phpmailer']->mock_sent[0]['to'][0][0]);
		$this->assertEquals('another_address@different-tld.com', $GLOBALS['phpmailer']->mock_sent[0]['to'][1][0]);
		$this->assertEquals($message . "\n", $GLOBALS['phpmailer']->mock_sent[0]['body']);
	}

	/**
	 * @covers ::wp_mail
	 * @group mail
	 * @ticket 18463
	 */
	function test_wp_mail_to_address_no_name() {
		$to = "<address@tld.com>";
		$subject = "RFC2822 Testing";
		$message = "My RFC822 Test Message";

		wp_mail( $to, $subject, $message );

		$this->assertEquals('address@tld.com', $GLOBALS['phpmailer']->mock_sent[0]['to'][0][0]);
		$this->assertEquals($message . "\n", $GLOBALS['phpmailer']->mock_sent[0]['body']);
	}

	/**
	 * @covers ::wp_mail
	 * @group mail
	 * @ticket 23642
	 */
	function test_wp_mail_return_value() {
		// No errors
		$this->assertTrue( wp_mail( 'valid@address.com', 'subject', 'body' ) );

		// Non-fatal errors
		$this->assertTrue( wp_mail( 'valid@address.com', 'subject', 'body', "Cc: invalid-address\nBcc: @invalid.address", ABSPATH . '/non-existant-file.html' ) );

		// Fatal errors
		$this->assertFalse( wp_mail( 'invalid.address', 'subject', 'body', '', array() ) );
	}

}
