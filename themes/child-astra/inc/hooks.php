<?php
/**
 * Child Theme Hooks & Filters
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
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
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
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
	}
endif;
add_action( 'after_setup_theme', 'child_astra_setup' );

if ( ! function_exists( 'child_astra_render_top_bar' ) ) :
	/**
	 * Render the premium top bar via wp_body_open (works with block and classic parents).
	 */
	function child_astra_render_top_bar() {
		?>
		<div class="child-astra-top-bar">
			<div class="child-astra-top-bar-content">
				<span class="top-bar-badge">NEW</span>
				<span class="top-bar-text">🚀 Welcome to our new modular site! Experience lightning-fast speeds in <?php echo esc_html( child_astra_get_current_year() ); ?>.</span>
				<a href="#content" class="top-bar-btn">Explore Now</a>
			</div>
		</div>
		<?php
	}
endif;
add_action( 'wp_body_open', 'child_astra_render_top_bar', 5 );
