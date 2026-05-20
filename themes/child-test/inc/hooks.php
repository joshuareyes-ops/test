<?php
/**
 * Child Theme Hooks & Filters
 *
 * @package WordPress
 * @subpackage child-test
 */

if ( ! function_exists( 'child_test_body_classes' ) ) :
	/**
	 * Add custom body classes for styling hooks.
	 *
	 * @param array $classes Existing body classes.
	 * @return array Modified body classes.
	 */
	function child_test_body_classes( $classes ) {
		$classes[] = 'child-test-theme';
		return $classes;
	}
endif;
add_filter( 'body_class', 'child_test_body_classes' );

if ( ! function_exists( 'child_test_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function child_test_setup() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
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

		// Add support for wide and full alignment block layouts.
		add_theme_support( 'align-wide' );

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );
	}
endif;
add_action( 'after_setup_theme', 'child_test_setup' );

