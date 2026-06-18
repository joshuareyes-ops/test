<?php
/**
 * Child Theme Customizer settings
 *
 * @package WordPress
 * @subpackage child-test
 */

if ( ! function_exists( 'child_test_customize_register' ) ) :
	/**
	 * Register Customizer settings, sections, and controls.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	function child_test_customize_register( $wp_customize ) {
		// Customizer configurations can be added here.
	}
endif;
add_action( 'customize_register', 'child_test_customize_register' );
