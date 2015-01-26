<?php

/**
 * @group phpunit
 */
class Tests_TestHelpers extends WP_UnitTestCase {
	/**
	 * @ticket 30522
	 */
	function data_assertEqualSets() {
		return array(
			array(
				array( 1, 2, 3 ), // test expected
				array( 1, 2, 3 ), // test actual
				false             // exception expected
			),
			array(
				array( 1, 2, 3 ),
				array( 2, 3, 1 ),
				false
			),
			array(
				array( 1, 2, 3 ),
				array( 1, 2, 3, 4 ),
				true
			),
			array(
				array( 1, 2, 3, 4 ),
				array( 1, 2, 3 ),
				true
			),
			array(
				array( 1, 2, 3 ),
				array( 3, 4, 2, 1 ),
				true
			),
			array(
				array( 1, 2, 3 ),
				array( 1, 2, 3, 3 ),
				true
			),
			array(
				array( 1, 2, 3 ),
				array( 2, 3, 1, 3 ),
				true
			),
		);
	}

	/**
	 * @dataProvider data_assertEqualSets
	 * @ticket 30522
	 */
	function test_assertEqualSets( $expected, $actual, $exception ) {
		if ( $exception ) {
			try {
				$this->assertEqualSets( $expected, $actual );
			} catch ( PHPUnit_Framework_ExpectationFailedException $ex ) {
				return;
			}

			$this->fail();
		} else {
			$this->assertEqualSets( $expected, $actual );
		}
	}

	/**
	 * @ticket 30522
	 */
	function data_assertEqualSetsWithIndex() {
		return array(
			array(
				array( 1, 2, 3 ), // test expected
				array( 1, 2, 3 ), // test actual
				false             // exception expected
			),
			array(
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				false
			),
			array(
				array( 1, 2, 3 ),
				array( 2, 3, 1 ),
				true
			),
			array(
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				array( 'b' => 2, 'c' => 3, 'a' => 1 ),
				false
			),
			array(
				array( 1, 2, 3 ),
				array( 1, 2, 3, 4 ),
				true
			),
			array(
				array( 1, 2, 3, 4 ),
				array( 1, 2, 3 ),
				true
			),
			array(
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				array( 'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4 ),
				true
			),
			array(
				array( 'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4 ),
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				true
			),
			array(
				array( 1, 2, 3 ),
				array( 3, 4, 2, 1 ),
				true
			),
			array(
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				array( 'c' => 3, 'b' => 2, 'd' => 4, 'a' => 1 ),
				true
			),
			array(
				array( 1, 2, 3 ),
				array( 1, 2, 3, 3 ),
				true
			),
			array(
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				array( 'a' => 1, 'b' => 2, 'c' => 3, 'd' => 3 ),
				true
			),
			array(
				array( 1, 2, 3 ),
				array( 2, 3, 1, 3 ),
				true
			),
			array(
				array( 'a' => 1, 'b' => 2, 'c' => 3 ),
				array( 'c' => 3, 'b' => 2, 'd' => 3, 'a' => 1 ),
				true
			),
		);
	}
	/**
	 * @dataProvider data_assertEqualSetsWithIndex
	 * @ticket 30522
	 */
	function test_assertEqualSetsWithIndex( $expected, $actual, $exception ) {
		if ( $exception ) {
			try {
				$this->assertEqualSetsWithIndex( $expected, $actual );
			} catch ( PHPUnit_Framework_ExpectationFailedException $ex ) {
				return;
			}

			$this->fail();
		} else {
			$this->assertEqualSetsWithIndex( $expected, $actual );
		}
	}

	public function test__unregister_post_status() {
		register_post_status( 'foo' );
		_unregister_post_status( 'foo' );

		$stati = get_post_stati();

		$this->assertFalse( isset( $stati['foo'] ) );
	}
}
