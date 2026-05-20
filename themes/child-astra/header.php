<?php
/**
 * The header for Astra Child Theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package child-astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?><!DOCTYPE html>
<?php astra_html_before(); ?>
<html <?php language_attributes(); ?>>
<head>
<?php astra_head_top(); ?>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
if ( apply_filters( 'astra_header_profile_gmpg_link', true ) ) {
	?>
	<link rel="profile" href="https://gmpg.org/xfn/11"> 
	<?php
}
?>
<?php wp_head(); ?>
<?php astra_head_bottom(); ?>
</head>

<body <?php astra_schema_body(); ?> <?php body_class(); ?>>
<?php astra_body_top(); ?>
<?php wp_body_open(); ?>

<!-- Custom Premium Child Theme Top Bar Notification -->
<div class="child-astra-top-bar">
	<div class="child-astra-top-bar-content">
		<span class="top-bar-badge">NEW</span>
		<span class="top-bar-text">🚀 Welcome to our new modular site! Experience lightning-fast speeds in <?php echo esc_html( child_astra_get_current_year() ); ?>.</span>
		<a href="#content" class="top-bar-btn">Explore Now</a>
	</div>
</div>

<a
	class="skip-link screen-reader-text"
	href="#content">
		<?php echo esc_html( astra_default_strings( 'string-header-skip-link', false ) ); ?>
</a>

<div
<?php
	echo wp_kses_post(
		astra_attr(
			'site',
			array(
				'id'    => 'page',
				'class' => 'hfeed site',
			)
		)
	);
	?>
>
	<?php
	// CUSTOM CHILD THEME HOOK: Before standard header starts
	do_action( 'child_astra_before_header' );

	astra_header_before();

	astra_header();

	astra_header_after();

	// CUSTOM CHILD THEME HOOK: After standard header ends
	do_action( 'child_astra_after_header' );

	astra_content_before();
	?>
	<div id="content" class="site-content">
		<div class="ast-container">
		<?php astra_content_top(); ?>
