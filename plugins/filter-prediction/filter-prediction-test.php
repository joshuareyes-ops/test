<?php
/*
Plugin Name: Filter Prediction Test
Description: Verifying filter execution order and string manipulation.
Version: 1.0
*/

// 1. First filter at priority 10
add_filter( 'the_content', 'add_first_div_wrapper', 10 );
function add_first_div_wrapper( $content ) {
    return '<div class="priority-10-wrapper">' . $content . '</div>';
}

// 2. Second filter at priority 10 (Registered after, so it runs after)
add_filter( 'the_content', 'strip_all_divs', 10 );
function strip_all_divs( $content ) {
    // A simple regex to strip all opening and closing div tags
    return preg_replace('/<\/?div[^>]*\>/i', '', $content);
}

// 3. Third filter at priority 20 (Runs last)
add_filter( 'the_content', 'add_final_div_wrapper', 20 );
function add_final_div_wrapper( $content ) {
    return '<div class="priority-20-wrapper">' . $content . '</div>';
}