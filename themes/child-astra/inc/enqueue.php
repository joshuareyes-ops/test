<?php
/**
 * Child Theme Enqueues for Astra Child Theme
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
		// Enqueue the child theme's style.css with parent dependency
		wp_enqueue_style( 
			'astra-child-theme-css', 
			get_stylesheet_directory_uri() . '/style.css', 
			array( 'astra-theme-css' ), // Dependency ensures parent styles load first
			ASTRA_CHILD_THEME_VERSION 
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'child_astra_enqueue_styles', 15 );
