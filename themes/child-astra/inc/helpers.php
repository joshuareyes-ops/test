<?php
/**
 * Child Theme Helper Functions for Astra Child Theme
 *
 * @package WordPress
 * @subpackage child-astra
 */

if ( ! function_exists( 'child_astra_get_current_year' ) ) :
	/**
	 * Retrieve the current year (useful for copyright statements).
	 *
	 * @return string Current year.
	 */
	function child_astra_get_current_year() {
		return date( 'Y' );
	}
endif;
