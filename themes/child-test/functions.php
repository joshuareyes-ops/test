<?php
/**
 * Child-test functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage child-test
 */

// Modular includes.
require_once get_stylesheet_directory () . '/inc/enqueue.php';
require_once get_stylesheet_directory() . '/inc/customizer.php';
require_once get_stylesheet_directory() . '/inc/hooks.php';
require_once get_stylesheet_directory() . '/inc/helpers.php';
require_once get_stylesheet_directory() . '/inc/admin-settings.php';

add_action( 'rest_api_init', 'my_theme_register_custom_endpoint' );

function my_theme_register_custom_endpoint() {
    register_rest_route( 'mytheme/v1', '/filtered-posts', array(
        'methods'             => WP_REST_Server::READABLE, // Equivalent to 'GET'
        'callback'            => 'my_theme_get_filtered_posts_callback',
        // Check if logged in and admin type
        'permission_callback' => function( WP_REST_Request $request ) {
        return current_user_can( 'manage_options' );
    }
    ) );
}

function my_theme_get_filtered_posts_callback( WP_REST_Request $request ) {
    // 1. Extract the query parameter (e.g., ?category_name=tech)
    $category_slug = $request->get_param( 'category_name' );

    // 2. Build the query arguments
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 10,
    );

    // Apply the filter only if the parameter was provided
    if ( ! empty( $category_slug ) ) {
        $args['category_name'] = sanitize_text_field( $category_slug );
    }

    // 3. Execute the query
    $query = new WP_Query( $args );
    $custom_response = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            // 4. Shape the custom JSON payload
            $custom_response[] = array(
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'url'   => get_permalink(),
            );
        }
        wp_reset_postdata();
    }

    // 5. Safely return the data as a JSON response
    return rest_ensure_response( $custom_response );
}

add_filter( 'the_content', 'my_theme_inject_app_container' );

function my_theme_inject_app_container( $content ) {
    // Only inject this on standard pages, not blog posts or archives
    if ( is_page() ) {
        $app_container = '<div id="custom-posts-container">Fetching data...</div>';
        // Append our container to the standard WordPress content
        return $content . $app_container; 
    }
    return $content;
}

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_api_scripts' );

function my_theme_enqueue_api_scripts() {
    // 1. Tell WordPress to load your specific JavaScript file
    wp_enqueue_script( 
        'my-api-fetch-script', 
        get_stylesheet_directory_uri() . '/assets/js/api-fetch.js', 
        array(), 
        '1.0', 
        true // Load in the footer
    );

    // 2. Inject the dynamic PHP data into the browser for JavaScript to use
    wp_localize_script( 
        'my-api-fetch-script', 
        'wpApiSettings', 
        array(
            'root'  => esc_url_raw( rest_url() ),     
            'nonce' => wp_create_nonce( 'wp_rest' )   
        )
    );
}