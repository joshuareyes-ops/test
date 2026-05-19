<?php
/**
 * Plugin Name: Custom Greeting Utility
 * Description: Demonstrates how to create and use custom do_action and apply_filters hooks.
 * Version: 1.0.0
 * Author: Developer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * We register a shortcode [custom_greeting] to easily test this
 * by adding [custom_greeting] to any post or page.
 */
add_shortcode( 'custom_greeting', 'cgu_render_greeting_box' );

function cgu_render_greeting_box() {
    // Generates HTML to return to WordPress
    ob_start();

    // CUSTOM FILTER HOOK: 'cgu_greeting_word'
    // Set the default word to "Hello". But before we use it, 
    // Passed it through apply_filters() so other developers can change it
    
    $default_word = 'Hello';
    $final_word   = apply_filters( 'cgu_greeting_word', $default_word );

    // Now we print the box using whatever word survived the filter pipeline
    echo '<div style="background: #eef2ff; border: 2px solid #6366f1; padding: 20px; border-radius: 8px; margin: 20px 0;">';
    echo '<h2 style="margin-top: 0;">' . esc_html( $final_word ) . ', Visitor!</h2>';
    echo '<p>Welcome to our website. We are glad you are here.</p>';
    echo '</div>';

    // CUSTOM ACTION HOOK: 'cgu_after_greeting'
    // Announce to the system that this event has occurred, 
    // in case anyone wants to react to it.
    do_action( 'cgu_after_greeting' );

    // Return the generated HTML to the shortcode
    return ob_get_clean();
}


/**
 * 1. Let's use the Filter to change the text!
 */
add_filter( 'cgu_greeting_word', 'my_custom_cowboy_greeting' );

function my_custom_cowboy_greeting( $word ) {
    // We receive 'Hello' in the $word variable, but we ignore it and return 'Howdy'
    return 'Howdy';
}

/**
 * 2. Let's use the Action to add something after the box!
 */
add_action( 'cgu_after_greeting', 'my_custom_button_injection' );

function my_custom_button_injection() {
    // Because this is an action, we don't return data. We just DO something.
    // In this case, we echo a button directly to the screen.
    echo '<button style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">';
    echo 'Claim Your Discount!';
    echo '</button>';
}
