<?php
/**
 * Child Theme Enqueues
 *
 * @package WordPress
 * @subpackage child-test
 */

if ( ! function_exists( 'child_test_enqueue_styles' ) ) :
	/**
	 * Enqueue parent and child stylesheets.
	 */
	function child_test_enqueue_styles() {
		// Enqueue parent style
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->parent()->get( 'Version' )
		);

		// Enqueue child style
		wp_enqueue_style(
			'child-test-style',
			get_stylesheet_uri(),
			array( 'twentytwentyfive-style' ),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'child_test_enqueue_styles' );
