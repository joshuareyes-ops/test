<?php
/**
 * Child Theme Customizer settings for Astra Child Theme
 *
 * @package WordPress
 * @subpackage child-astra
 */

if ( ! function_exists( 'child_astra_customize_register' ) ) :
	/**
	 * Register Customizer settings, sections, and controls.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	function child_astra_customize_register( $wp_customize ) {
		// Customizer configurations can be added here
	}
endif;
add_action( 'customize_register', 'child_astra_customize_register' );
