<?php
/**
 * Child Theme Helper Functions
 *
 * @package WordPress
 * @subpackage child-test
 */

if ( ! function_exists( 'child_test_get_current_year' ) ) :
	/**
	 * Retrieve the current year (useful for copyright statements).
	 *
	 * @return string Current year.
	 */
	function child_test_get_current_year() {
		return date( 'Y' );
	}
endif;
