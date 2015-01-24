<?php

class Tests_Query extends WP_UnitTestCase {

	function setUp() {
		global $wp_rewrite;
		parent::setUp();

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );

		create_initial_taxonomies();

		$wp_rewrite->flush_rules();
	}

	/**
	 * @covers WP_Query
	 * @ticket 24785
	 */
	function test_nested_loop_reset_postdata() {
		$post_id = $this->factory->post->create();
		$nested_post_id = $this->factory->post->create();

		$first_query = new WP_Query( array( 'post__in' => array( $post_id ) ) );
		while ( $first_query->have_posts() ) { $first_query->the_post();
			$second_query = new WP_Query( array( 'post__in' => array( $nested_post_id ) ) );
			while ( $second_query->have_posts() ) {
				$second_query->the_post();
				$this->assertEquals( get_the_ID(), $nested_post_id );
			}
			$first_query->reset_postdata();
			$this->assertEquals( get_the_ID(), $post_id );
		}
	}

	/**
	 * @covers WP_Query
	 * @ticket 16471
	 */
	function test_default_query_var() {
		$query = new WP_Query;
		$this->assertEquals( '', $query->get( 'nonexistent' ) );
		$this->assertFalse( $query->get( 'nonexistent', false ) );
		$this->assertTrue( $query->get( 'nonexistent', true ) );
	}

	/**
	 * @covers WP_Query
	 * @ticket 25380
	 */
	function test_pre_posts_per_page() {
		$this->factory->post->create_many( 10 );

		add_action( 'pre_get_posts', array( $this, 'filter_posts_per_page' ) );

		$this->go_to( get_feed_link() );

		$this->assertEquals( 30, get_query_var( 'posts_per_page' ) );
	}

	function filter_posts_per_page( &$query ) {
		$query->set( 'posts_per_rss', 30 );
	}

	/**
	 * @covers WP_Query
	 * @ticket 26627
	 */
	function test_tag_queried_object() {
		$slug = 'tag-slug-26627';
		$this->factory->tag->create( array( 'slug' => $slug ) );
		$tag = get_term_by( 'slug', $slug, 'post_tag' );

		add_action( 'pre_get_posts', array( $this, '_tag_queried_object' ), 11 );

		$this->go_to( get_term_link( $tag ) );

		$this->assertQueryTrue( 'is_tag', 'is_archive' );
		$this->assertNotEmpty( get_query_var( 'tag_id' ) );
		$this->assertNotEmpty( get_query_var( 'tag' ) );
		$this->assertEmpty( get_query_var( 'tax_query' ) );
		$this->assertCount( 1, get_query_var( 'tag_slug__in' ) );
		$this->assertEquals( get_queried_object(), $tag );

		remove_action( 'pre_get_posts', array( $this, '_tag_queried_object' ), 11 );
	}

	function _tag_queried_object( &$query ) {
		$tag = get_term_by( 'slug', 'tag-slug-26627', 'post_tag' );
		$this->assertTrue( $query->is_tag() );
		$this->assertTrue( $query->is_archive() );
		$this->assertNotEmpty( $query->get( 'tag' ) );
		$this->assertCount( 1, $query->get( 'tag_slug__in' ) );
		$this->assertEquals( $query->get_queried_object(), $tag );
	}
}
