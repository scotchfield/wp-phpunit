<?php

/**
 * @group hooks
 */
class Tests_Plugin extends WP_UnitTestCase {

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 */
	function test_simple_action() {
		$a = new MockAction();
		$tag = 'test_action';

		add_action($tag, array(&$a, 'action'));
		do_action($tag);

		// only one event occurred for the hook, with empty args
		$this->assertEquals(1, $a->get_call_count());
		// only our hook was called
		$this->assertEquals(array($tag), $a->get_tags());

		$argsvar = $a->get_args();
		$args = array_pop( $argsvar );
		$this->assertEquals(array(''), $args);
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 * @covers ::remove_action
	 */
	function test_remove_action() {
		$a = new MockAction();
		$tag = rand_str();

		add_action($tag, array(&$a, 'action'));
		do_action($tag);

		// make sure our hook was called correctly
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());

		// now remove the action, do it again, and make sure it's not called this time
		remove_action($tag, array(&$a, 'action'));
		do_action($tag);
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());

	}

	/**
	 * @covers ::add_action
	 * @covers ::has_action
	 * @covers ::remove_action
	 */
	function test_has_action() {
		$tag = rand_str();
		$func = rand_str();

		$this->assertFalse( has_action($tag, $func) );
		$this->assertFalse( has_action($tag) );
		add_action($tag, $func);
		$this->assertEquals( 10, has_action($tag, $func) );
		$this->assertTrue( has_action($tag) );
		remove_action($tag, $func);
		$this->assertFalse( has_action($tag, $func) );
		$this->assertFalse( has_action($tag) );
	}

	/**
	 * one tag with multiple actions
	 *
	 * @covers ::add_action
	 * @covers ::do_action
	 */
	function test_multiple_actions() {
		$a1 = new MockAction();
		$a2 = new MockAction();
		$tag = rand_str();

		// add both actions to the hook
		add_action($tag, array(&$a1, 'action'));
		add_action($tag, array(&$a2, 'action'));

		do_action($tag);

		// both actions called once each
		$this->assertEquals(1, $a1->get_call_count());
		$this->assertEquals(1, $a2->get_call_count());
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 */
	function test_action_args_1() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		add_action($tag, array(&$a, 'action'));
		// call the action with a single argument
		do_action($tag, $val);

		$call_count = $a->get_call_count();
		$this->assertEquals(1, $call_count);
		$argsvar = $a->get_args();
		$this->assertEquals( array( $val ), array_pop( $argsvar ) );
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 */
	function test_action_args_2() {
		$a1 = new MockAction();
		$a2 = new MockAction();
		$tag = rand_str();
		$val1 = rand_str();
		$val2 = rand_str();

		// a1 accepts two arguments, a2 doesn't
		add_action($tag, array(&$a1, 'action'), 10, 2);
		add_action($tag, array(&$a2, 'action'));
		// call the action with two arguments
		do_action($tag, $val1, $val2);

		$call_count = $a1->get_call_count();
		// a1 should be called with both args
		$this->assertEquals(1, $call_count);
		$argsvar1 = $a1->get_args();
		$this->assertEquals( array( $val1, $val2 ), array_pop( $argsvar1 ) );

		// a2 should be called with one only
		$this->assertEquals(1, $a2->get_call_count());
		$argsvar2 = $a2->get_args();
		$this->assertEquals( array( $val1 ), array_pop( $argsvar2 ) );
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 */
	function test_action_priority() {
		$a = new MockAction();
		$tag = rand_str();

		add_action($tag, array(&$a, 'action'), 10);
		add_action($tag, array(&$a, 'action2'), 9);
		do_action($tag);

		// two events, one per action
		$this->assertEquals(2, $a->get_call_count());

		$expected = array (
			// action2 is called first because it has priority 9
			array (
				'action' => 'action2',
				'tag' => $tag,
				'args' => array('')
			),
			// action 1 is called second
			array (
				'action' => 'action',
				'tag' => $tag,
				'args' => array('')
			),
		);

		$this->assertEquals($expected, $a->get_events());
	}

	/**
	 * @covers ::did_action
	 * @covers ::do_action
	 */
	function test_did_action() {
		$tag1 = rand_str();
		$tag2 = rand_str();

		// do action tag1 but not tag2
		do_action($tag1);
		$this->assertEquals(1, did_action($tag1));
		$this->assertEquals(0, did_action($tag2));

		// do action tag2 a random number of times
		$count = rand(0, 10);
		for ($i=0; $i<$count; $i++)
			do_action($tag2);

		// tag1's count hasn't changed, tag2 should be correct
		$this->assertEquals(1, did_action($tag1));
		$this->assertEquals($count, did_action($tag2));

	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 * @covers ::remove_action
	 */
	function test_all_action() {
		$a = new MockAction();
		$tag1 = rand_str();
		$tag2 = rand_str();

		// add an 'all' action
		add_action('all', array(&$a, 'action'));
		$this->assertEquals(10, has_filter('all', array(&$a, 'action')));
		// do some actions
		do_action($tag1);
		do_action($tag2);
		do_action($tag1);
		do_action($tag1);

		// our action should have been called once for each tag
		$this->assertEquals(4, $a->get_call_count());
		// only our hook was called
		$this->assertEquals(array($tag1, $tag2, $tag1, $tag1), $a->get_tags());

		remove_action('all', array(&$a, 'action'));
		$this->assertFalse(has_filter('all', array(&$a, 'action')));

	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 * @covers ::remove_action
	 */
	function test_remove_all_action() {
		$a = new MockAction();
		$tag = rand_str();

		add_action('all', array(&$a, 'action'));
		$this->assertEquals(10, has_filter('all', array(&$a, 'action')));
		do_action($tag);

		// make sure our hook was called correctly
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());

		// now remove the action, do it again, and make sure it's not called this time
		remove_action('all', array(&$a, 'action'));
		$this->assertFalse(has_filter('all', array(&$a, 'action')));
		do_action($tag);
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action_ref_array
	 */
	function test_action_ref_array() {
		$obj = new stdClass();
		$a = new MockAction();
		$tag = rand_str();

		add_action($tag, array(&$a, 'action'));

		do_action_ref_array($tag, array(&$obj));

		$args = $a->get_args();
		$this->assertSame($args[0][0], $obj);
		// just in case we don't trust assertSame
		$obj->foo = true;
		$this->assertFalse( empty($args[0][0]->foo) );
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 * @ticket 11241
	 */
	function test_action_keyed_array() {
		$a = new MockAction();

		$tag = rand_str();

		add_action($tag, array(&$a, 'action'));

		$context = array( rand_str() => rand_str() );
		do_action($tag, $context);

		$args = $a->get_args();
		$this->assertSame($args[0][0], $context);

		$context2 = array( rand_str() => rand_str(), rand_str() => rand_str() );
		do_action($tag, $context2);

		$args = $a->get_args();
		$this->assertSame($args[1][0], $context2);

	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 * @covers ::did_action
	 */
	function test_action_self_removal() {
		add_action( 'test_action_self_removal', array( $this, 'action_self_removal' ) );
		do_action( 'test_action_self_removal' );
		$this->assertEquals( 1, did_action( 'test_action_self_removal' ) );
	}

	function action_self_removal() {
		remove_action( 'test_action_self_removal', array( $this, 'action_self_removal' ) );
	}

	/**
	 * Make sure current_action() behaves as current_filter()
	 *
	 * @covers ::current_action
	 * @ticket 14994
	 */
	function test_current_action() {
		global $wp_current_filter;
		$wp_current_filter[] = 'first';
		$wp_current_filter[] = 'second'; // Let's say a second action was invoked.

		$this->assertEquals( 'second', current_action() );
	}

	/**
	 * @covers ::doing_filter
	 * @ticket 14994
	 */
	function test_doing_filter() {
		global $wp_current_filter;
		$wp_current_filter = array(); // Set to an empty array first

		$this->assertFalse( doing_filter() ); // No filter is passed in, and no filter is being processed
		$this->assertFalse( doing_filter( 'testing' ) ); // Filter is passed in but not being processed

		$wp_current_filter[] = 'testing';

		$this->assertTrue( doing_filter() ); // No action is passed in, and a filter is being processed
		$this->assertTrue( doing_filter( 'testing') ); // Filter is passed in and is being processed
		$this->assertFalse( doing_filter( 'something_else' ) ); // Filter is passed in but not being processed

		$wp_current_filter = array();
	}

	/**
	 * @covers ::doing_action
	 * @ticket 14994
	 */
	function test_doing_action() {
		global $wp_current_filter;
		$wp_current_filter = array(); // Set to an empty array first

		$this->assertFalse( doing_action() ); // No action is passed in, and no filter is being processed
		$this->assertFalse( doing_action( 'testing' ) ); // Action is passed in but not being processed

		$wp_current_filter[] = 'testing';

		$this->assertTrue( doing_action() ); // No action is passed in, and a filter is being processed
		$this->assertTrue( doing_action( 'testing') ); // Action is passed in and is being processed
		$this->assertFalse( doing_action( 'something_else' ) ); // Action is passed in but not being processed

		$wp_current_filter = array();
	}

	/**
	 * @covers ::doing_filter
	 * @covers ::has_action
	 * @ticket 14994
	 */
	function test_doing_filter_real() {
		$this->assertFalse( doing_filter() ); // No filter is passed in, and no filter is being processed
		$this->assertFalse( doing_filter( 'testing' ) ); // Filter is passed in but not being processed

		add_filter( 'testing', array( $this, 'apply_testing_filter' ) );
		$this->assertTrue( has_action( 'testing' ) );
		$this->assertEquals( 10, has_action( 'testing', array( $this, 'apply_testing_filter' ) ) );

		apply_filters( 'testing', '' );

		// Make sure it ran.
		$this->assertTrue( $this->apply_testing_filter );

		$this->assertFalse( doing_filter() ); // No longer doing any filters
		$this->assertFalse( doing_filter( 'testing' ) ); // No longer doing this filter
	}

	/**
	 * @covers ::doing_filter
	 * @covers ::has_action
	 */
	function apply_testing_filter() {
		$this->apply_testing_filter = true;

		$this->assertTrue( doing_filter() );
		$this->assertTrue( doing_filter( 'testing' ) );
		$this->assertFalse( doing_filter( 'something_else' ) );
		$this->assertFalse( doing_filter( 'testing_nested' ) );

		add_filter( 'testing_nested', array( $this, 'apply_testing_nested_filter' ) );
		$this->assertTrue( has_action( 'testing_nested' ) );
		$this->assertEquals( 10, has_action( 'testing_nested', array( $this, 'apply_testing_nested_filter' ) ) );

		apply_filters( 'testing_nested', '' );

		// Make sure it ran.
		$this->assertTrue( $this->apply_testing_nested_filter );

		$this->assertFalse( doing_filter( 'testing_nested' ) );
		$this->assertFalse( doing_filter( 'testing_nested' ) );
	}

	/**
	 * @covers ::doing_filter
	 */
	function apply_testing_nested_filter() {
		$this->apply_testing_nested_filter = true;
		$this->assertTrue( doing_filter() );
		$this->assertTrue( doing_filter( 'testing' ) );
		$this->assertTrue( doing_filter( 'testing_nested' ) );
		$this->assertFalse( doing_filter( 'something_else' ) );
	}

	/**
	 * @covers ::has_action
	 * @ticket 23265
	 */
	function test_callback_representations() {
		$tag = __FUNCTION__;

		$this->assertFalse( has_action( $tag ) );

		add_action( $tag, array( 'Class', 'method' ) );

		$this->assertEquals( 10, has_action( $tag, array( 'Class', 'method' ) ) );

		$this->assertEquals( 10, has_action( $tag, 'Class::method' ) );
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 * @covers ::remove_action
	 * @ticket 10493
	 */
	function test_action_closure() {
		$tag = 'test_action_closure';
		$closure = function($a, $b) { $GLOBALS[$a] = $b;};
		add_action($tag, $closure, 10, 2);

		$this->assertSame( 10, has_action($tag, $closure) );

		$context = array( rand_str(), rand_str() );
		do_action($tag, $context[0], $context[1]);

		$this->assertSame($GLOBALS[$context[0]], $context[1]);

		$tag2 = 'test_action_closure_2';
		$closure2 = function() { $GLOBALS['closure_no_args'] = true;};
		add_action($tag2, $closure2);

		$this->assertSame( 10, has_action($tag2, $closure2) );

		do_action($tag2);

		$this->assertTrue($GLOBALS['closure_no_args']);

		remove_action( $tag, $closure );
		remove_action( $tag2, $closure2 );
	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 */
	function test_simple_filter() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		add_filter($tag, array($a, 'filter'));
		$this->assertEquals($val, apply_filters($tag, $val));

		// only one event occurred for the hook, with empty args
		$this->assertEquals(1, $a->get_call_count());
		// only our hook was called
		$this->assertEquals(array($tag), $a->get_tags());

		$argsvar = $a->get_args();
		$args = array_pop( $argsvar );
		$this->assertEquals(array($val), $args);
	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 * @covers ::remove_filter
	 */
	function test_remove_filter() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		add_filter($tag, array($a, 'filter'));
		$this->assertEquals($val, apply_filters($tag, $val));

		// make sure our hook was called correctly
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());

		// now remove the filter, do it again, and make sure it's not called this time
		remove_filter($tag, array($a, 'filter'));
		$this->assertEquals($val, apply_filters($tag, $val));
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());

	}

	/**
	 * @covers ::add_filter
	 * @covers ::has_filter
	 */
	function test_has_filter() {
			$tag = rand_str();
			$func = rand_str();

			$this->assertFalse( has_filter($tag, $func) );
			$this->assertFalse( has_filter($tag) );
			add_filter($tag, $func);
			$this->assertEquals( 10, has_filter($tag, $func) );
			$this->assertTrue( has_filter($tag) );
			remove_filter($tag, $func);
			$this->assertFalse( has_filter($tag, $func) );
			$this->assertFalse( has_filter($tag) );
	}

	/**
	 * one tag with multiple filters
	 *
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 */
	function test_multiple_filters() {
		$a1 = new MockAction();
		$a2 = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		// add both filters to the hook
		add_filter($tag, array($a1, 'filter'));
		add_filter($tag, array($a2, 'filter'));

		$this->assertEquals($val, apply_filters($tag, $val));

		// both filters called once each
		$this->assertEquals(1, $a1->get_call_count());
		$this->assertEquals(1, $a2->get_call_count());
	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 */
	function test_filter_args_1() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();
		$arg1 = rand_str();

		add_filter($tag, array($a, 'filter'), 10, 2);
		// call the filter with a single argument
		$this->assertEquals($val, apply_filters($tag, $val, $arg1));

		$this->assertEquals(1, $a->get_call_count());
		$argsvar = $a->get_args();
		$this->assertEquals( array( $val, $arg1 ), array_pop( $argsvar ) );
	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 */
	function test_filter_args_2() {
		$a1 = new MockAction();
		$a2 = new MockAction();
		$tag = rand_str();
		$val = rand_str();
		$arg1 = rand_str();
		$arg2 = rand_str();

		// a1 accepts two arguments, a2 doesn't
		add_filter($tag, array($a1, 'filter'), 10, 3);
		add_filter($tag, array($a2, 'filter'));
		// call the filter with two arguments
		$this->assertEquals($val, apply_filters($tag, $val, $arg1, $arg2));

		// a1 should be called with both args
		$this->assertEquals(1, $a1->get_call_count());
		$argsvar1 = $a1->get_args();
		$this->assertEquals( array( $val, $arg1, $arg2 ), array_pop( $argsvar1 ) );

		// a2 should be called with one only
		$this->assertEquals(1, $a2->get_call_count());
		$argsvar2 = $a2->get_args();
		$this->assertEquals( array( $val ), array_pop( $argsvar2 ) );
	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 */
	function test_filter_priority() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		// make two filters with different priorities
		add_filter($tag, array($a, 'filter'), 10);
		add_filter($tag, array($a, 'filter2'), 9);
		$this->assertEquals($val, apply_filters($tag, $val));

		// there should be two events, one per filter
		$this->assertEquals(2, $a->get_call_count());

		$expected = array (
			// filter2 is called first because it has priority 9
			array (
				'filter' => 'filter2',
				'tag' => $tag,
				'args' => array($val)
			),
			// filter 1 is called second
			array (
				'filter' => 'filter',
				'tag' => $tag,
				'args' => array($val)
			),
		);

		$this->assertEquals($expected, $a->get_events());
	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 * @covers ::has_filter
	 * @covers ::remove_filter
	 */
	function test_all_filter() {
		$a = new MockAction();
		$tag1 = rand_str();
		$tag2 = rand_str();
		$val = rand_str();

		// add an 'all' filter
		add_filter('all', array($a, 'filterall'));
		// do some filters
		$this->assertEquals($val, apply_filters($tag1, $val));
		$this->assertEquals($val, apply_filters($tag2, $val));
		$this->assertEquals($val, apply_filters($tag1, $val));
		$this->assertEquals($val, apply_filters($tag1, $val));

		// our filter should have been called once for each apply_filters call
		$this->assertEquals(4, $a->get_call_count());
		// the right hooks should have been called in order
		$this->assertEquals(array($tag1, $tag2, $tag1, $tag1), $a->get_tags());

		remove_filter('all', array($a, 'filterall'));
		$this->assertFalse( has_filter('all', array($a, 'filterall')) );

	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters
	 * @covers ::has_filter
	 * @covers ::remove_filter
	 */
	function test_remove_all_filter() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		add_filter('all', array($a, 'filterall'));
		$this->assertTrue( has_filter('all') );
		$this->assertEquals( 10, has_filter('all', array($a, 'filterall')) );
		$this->assertEquals($val, apply_filters($tag, $val));

		// make sure our hook was called correctly
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());

		// now remove the filter, do it again, and make sure it's not called this time
		remove_filter('all', array($a, 'filterall'));
		$this->assertFalse( has_filter('all', array($a, 'filterall')) );
		$this->assertFalse( has_filter('all') );
		$this->assertEquals($val, apply_filters($tag, $val));
		// call cound should remain at 1
		$this->assertEquals(1, $a->get_call_count());
		$this->assertEquals(array($tag), $a->get_tags());
	}

	/**
	 * @covers ::add_filter
	 * @covers ::has_filter
	 * @covers ::remove_all_filters
	 * @ticket 20920
	 */
	function test_remove_all_filters_should_respect_the_priority_argument() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		add_filter( $tag, array( $a, 'filter' ), 12 );
		$this->assertTrue( has_filter( $tag ) );

		// Should not be removed.
		remove_all_filters( $tag, 11 );
		$this->assertTrue( has_filter( $tag ) );

		remove_all_filters( $tag, 12 );
		$this->assertFalse( has_filter( $tag ) );
	}

	/**
	 * @covers ::add_action
	 * @covers ::apply_filters_ref_array
	 * @ticket 9886
	 */
	function test_filter_ref_array() {
		$obj = new stdClass();
		$a = new MockAction();
		$tag = rand_str();

		add_action($tag, array($a, 'filter'));

		apply_filters_ref_array($tag, array(&$obj));

		$args = $a->get_args();
		$this->assertSame($args[0][0], $obj);
		// just in case we don't trust assertSame
		$obj->foo = true;
		$this->assertFalse( empty($args[0][0]->foo) );
	}

	/**
	 * @covers ::add_filter
	 * @covers ::apply_filters_ref_array
	 * @ticket 12723
	 */
	function test_filter_ref_array_result() {
		$obj = new stdClass();
		$a = new MockAction();
		$b = new MockAction();
		$tag = rand_str();

		add_action($tag, array($a, 'filter_append'), 10, 2);
		add_action($tag, array($b, 'filter_append'), 10, 2);

		$result = apply_filters_ref_array($tag, array('string', &$obj));

		$this->assertEquals($result, 'string_append_append');

		$args = $a->get_args();
		$this->assertSame($args[0][1], $obj);
		// just in case we don't trust assertSame
		$obj->foo = true;
		$this->assertFalse( empty($args[0][1]->foo) );

		$args = $b->get_args();
		$this->assertSame($args[0][1], $obj);
		// just in case we don't trust assertSame
		$obj->foo = true;
		$this->assertFalse( empty($args[0][1]->foo) );

	}

	function _self_removal($tag) {
		remove_action( $tag, array($this, '_self_removal'), 10, 1 );
		return $tag;
	}

	/**
	 * @covers ::add_filter
	 * @covers ::has_filter
	 * @covers ::remove_all_filters
	 * @ticket 29070
	 */
	function test_has_filter_after_remove_all_filters() {
		$a = new MockAction();
		$tag = rand_str();
		$val = rand_str();

		// No priority
		add_filter( $tag, array( $a, 'filter' ), 11 );
		add_filter( $tag, array( $a, 'filter' ), 12 );
		$this->assertTrue( has_filter( $tag ) );

		remove_all_filters( $tag );
		$this->assertFalse( has_filter( $tag ) );

		// Remove priorities one at a time
		add_filter( $tag, array( $a, 'filter' ), 11 );
		add_filter( $tag, array( $a, 'filter' ), 12 );
		$this->assertTrue( has_filter( $tag ) );

		remove_all_filters( $tag, 11 );
		remove_all_filters( $tag, 12 );
		$this->assertFalse( has_filter( $tag ) );
	}

	/**
	 * @covers ::add_action
	 * @covers ::do_action
	 * @ticket 29070
	 */
	 function test_has_filter_doesnt_reset_wp_filter() {
		add_action( 'action_test_has_filter_doesnt_reset_wp_filter', '__return_null', 1 );
		add_action( 'action_test_has_filter_doesnt_reset_wp_filter', '__return_null', 2 );
		add_action( 'action_test_has_filter_doesnt_reset_wp_filter', '__return_null', 3 );
		add_action( 'action_test_has_filter_doesnt_reset_wp_filter', array( $this, '_action_test_has_filter_doesnt_reset_wp_filter' ), 4 );

		do_action( 'action_test_has_filter_doesnt_reset_wp_filter' );
	 }

	 function _action_test_has_filter_doesnt_reset_wp_filter() {
		global $wp_filter;

		has_action( 'action_test_has_filter_doesnt_reset_wp_filter', '_function_that_doesnt_exist' );

		$filters = current( $wp_filter['action_test_has_filter_doesnt_reset_wp_filter'] );
		$the_ = current( $filters );
		$this->assertEquals( $the_['function'], array( $this, '_action_test_has_filter_doesnt_reset_wp_filter' ) );
	 }

}
