<?php
/**
 * Child-test functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage child-test
 */

 //Test
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

add_action( 'rest_api_init', 'my_theme_advanced_endpoint' );

function my_theme_advanced_endpoint() {
    register_rest_route( 'mytheme/v1', '/advanced-posts', array(
    // --- HANDLER 1: THE EXISTING GET REQUEST (PUBLIC) ---
    array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'my_theme_advanced_callback',
        'permission_callback' => '__return_true', 
        'args'                => array(
            // Parameter 1: Validated mathematically
            'author_id' => array(
                'required'          => false,
                'type'              => 'integer',
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param ) && intval( $param ) > 0;
                },
                'sanitize_callback' => 'absint',
            ),
            // Parameter 2: Validated by string length
            'search_term' => array(
                'required'          => false,
                'type'              => 'string',
                'validate_callback' => function( $param, $request, $key ) {
                    return is_string( $param ) && strlen( $param ) >= 3;
                },
                'sanitize_callback' => 'sanitize_text_field',
            ),
            // Parameter 3: Validated by strict boolean checking
            'strict_mode' => array(
                'required'          => false,
                'type'              => 'boolean',
                'validate_callback' => function( $param, $request, $key ) {
                    return is_bool( $param ) || in_array( $param, array( 'true', 'false', '1', '0' ), true );
                },
                'sanitize_callback' => 'rest_sanitize_boolean',
            ),
            // Standard Pagination Parameters
            'page' => array(
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'type'              => 'integer',
                'default'           => 5,
                'sanitize_callback' => 'absint',
            )
        )
    ),

    // --- HANDLER 2: PATCH REQUEST (AUTHENTICATED) ---
    array(
        'methods'             => 'PATCH', // Specifically listening for partial updates
        'callback'            => 'my_theme_advanced_patch_callback',
        'permission_callback' => function() {
            // Strictly locked to users who have editing privileges
            return current_user_can( 'edit_posts' ); 
        },
        'args'                => array(
            // For a PATCH request, we absolutely MUST know which post ID to update
            'id' => array(
                'required'          => true,
                'type'              => 'integer',
                'sanitize_callback' => 'absint'
            ),
            // The new title they want to save
            'title' => array(
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    )
    ) );
}

function my_theme_advanced_callback( WP_REST_Request $request ) {
    // 1. Extract the sanitized parameters
    $author_id   = $request->get_param( 'author_id' );
    $search_term = $request->get_param( 'search_term' );
    $strict_mode = $request->get_param( 'strict_mode' );
    $page        = $request->get_param( 'page' );
    $per_page    = $request->get_param( 'per_page' );

    // 2. Build the standard WP_Query arguments
    $args = array(
        'post_type'      => 'post',
        'paged'          => $page,
        'posts_per_page' => $per_page,
    );

    // Conditionally apply our complex filters
    if ( $author_id ) {
        $args['author'] = $author_id;
    }

    if ( $search_term ) {
        $args['s'] = $search_term;
        if ( $strict_mode ) {
            $args['exact'] = true; // Forces WP_Query to match the whole word exactly
        }
    }

    // 3. Execute the Query
    $query = new WP_Query( $args );
    $data  = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $data[] = array(
                'id'    => get_the_ID(),
                'title' => get_the_title(),
            );
        }
        wp_reset_postdata();
    }

    // 4. Construct the Official REST Response Object
    $response = new WP_REST_Response( $data, 200 );

    // 5. Inject official pagination headers
    $response->header( 'X-WP-Total', $query->found_posts );
    $response->header( 'X-WP-TotalPages', $query->max_num_pages );

    return $response;
}

function my_theme_advanced_patch_callback( WP_REST_Request $request ) {
    // 1. Extract the sanitized data
    $post_id   = $request->get_param( 'id' );
    $new_title = $request->get_param( 'title' );

    // 2. Verify the post actually exists before trying to update it
    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'no_post', 'Invalid post ID.', array( 'status' => 404 ) );
    }

    // 3. Prepare the update payload
    $update_data = array(
        'ID' => $post_id,
    );

    if ( ! empty( $new_title ) ) {
        $update_data['post_title'] = $new_title;
    }

    // 4. Execute the database update
    $updated_post_id = wp_update_post( $update_data, true );

    if ( is_wp_error( $updated_post_id ) ) {
        return $updated_post_id; // Return the exact error to the API response
    }

    // 5. Return success payload
    return new WP_REST_Response( array( 
        'message' => 'Post updated successfully.',
        'updated_id' => $updated_post_id 
    ), 200 );
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
    // Conditional loading: the #custom-posts-container is only injected on
    // pages (see my_theme_inject_app_container), so skip the script everywhere
    // else to avoid shipping unused JavaScript and an unnecessary REST nonce.
    if ( ! is_page() ) {
        return;
    }

    $script_path = get_stylesheet_directory() . '/assets/js/api-fetch.js';
    $script_uri  = get_stylesheet_directory_uri() . '/assets/js/api-fetch.js';

    // Version the asset off its last-modified time so browsers automatically
    // pick up changes instead of caching a stale file behind a fixed "1.0".
    $version = file_exists( $script_path ) ? filemtime( $script_path ) : false;

    // 1. Tell WordPress to load your specific JavaScript file.
    wp_enqueue_script(
        'my-api-fetch-script',
        $script_uri,
        array(),
        $version,
        array(
            'in_footer' => true,
            // The script only runs on DOMContentLoaded, so defer it to keep it
            // off the critical rendering path without breaking execution order.
            'strategy'  => 'defer',
        )
    );

    // 2. Inject the dynamic PHP data into the browser for JavaScript to use.
    wp_localize_script(
        'my-api-fetch-script',
        'wpApiSettings',
        array(
            'root'  => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        )
    );
}

// This is a function that gets the heavy post data from the database
function get_my_heavy_post_data() {
    // 1. Define a unique name for this specific cache (max 45 characters)
    $transient_key = 'my_theme_heavy_post_list';

    // 2. Try to get the data from the cache
    $cached_data = get_transient( $transient_key );

    // 3. Check if the cache missed (either it expired, or it was never created)
    if ( false === $cached_data ) {
        
        // --- THE EXPENSIVE OPERATION STARTS HERE ---
        // (This code ONLY runs if the cache is empty)
        
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 50,
            // A complex meta query that usually slows down the database
            'meta_query'     => array(
                array(
                    'key'     => 'featured_article',
                    'value'   => 'yes',
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query( $args );
        $cached_data = array(); 

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $cached_data[] = array(
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                );
            }
            wp_reset_postdata();
        }
        // --- THE EXPENSIVE OPERATION ENDS HERE ---

        // 4. Save the result to the cache so the next visitor doesn't trigger the query
        // We use WordPress time constants (e.g., 12 * HOUR_IN_SECONDS) for readability
        set_transient( $transient_key, $cached_data, 12 * HOUR_IN_SECONDS );
    }

    // 5. Return the data (whether it came from the DB just now, or from the cache)
    return $cached_data;
}