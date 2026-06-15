<?php
/**
 * Child Theme Enqueues
 *
 * @package WordPress
 * @subpackage child-astra
 */

if ( ! defined( 'ASTRA_CHILD_THEME_VERSION' ) ) {
	define( 'ASTRA_CHILD_THEME_VERSION', '1.0.0' );
}

if ( ! function_exists( 'child_astra_enqueue_styles' ) ) :
	/**
	 * Enqueue parent and child stylesheets.
	 */
	function child_astra_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->parent()->get( 'Version' )
		);

		wp_enqueue_style(
			'child-astra-style',
			get_stylesheet_uri(),
			array( 'twentytwentyfive-style' ),
			ASTRA_CHILD_THEME_VERSION
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'child_astra_enqueue_styles' );
