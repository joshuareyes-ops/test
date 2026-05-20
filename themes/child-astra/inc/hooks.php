<?php
/**
 * Child Theme Hooks & Filters for Astra Child Theme
 *
 * @package WordPress
 * @subpackage child-astra
 */

if ( ! function_exists( 'child_astra_body_classes' ) ) :
	/**
	 * Add custom body classes for styling hooks.
	 *
	 * @param array $classes Existing body classes.
	 * @return array Modified body classes.
	 */
	function child_astra_body_classes( $classes ) {
		$classes[] = 'child-astra-theme';
		return $classes;
	}
endif;
add_filter( 'body_class', 'child_astra_body_classes' );

if ( ! function_exists( 'child_astra_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function child_astra_setup() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup to HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Add support for core custom logo.
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'child_astra_setup' );

/**
 * Challenge: Using the undocumented Astra Header Hook
 * Hook: 'astra_header_profile_gmpg_link'
 * Description: Astra uses this filter in header.php to determine if it should render the 
 * <link rel="profile" href="https://gmpg.org/xfn/11"> tag. We return false here to disable 
 */
add_filter( 'astra_header_profile_gmpg_link', '__return_false' );
