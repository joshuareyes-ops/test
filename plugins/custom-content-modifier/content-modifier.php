<?php
/**
 * Plugin Name: Content Modifier
 * Description: A plugin that modifies the content of posts and pages.
 * Version: 1.0.0
 * Author: Developer
 */

if (!defined("ABSPATH")) {
    exit;
}

// ──────────────────────────────────────────────
// BLOCK A: Inject CSS styles into the page <head>
// ──────────────────────────────────────────────

add_action( 'wp_head', 'ccm_inject_styles' );

function ccm_inject_styles() {
    // Only load CSS on single post/page views
    if ( ! is_singular() ) {
        return;
    }
    ?>
    <style>
        /* Shared base style for all CCM message boxes */
        .ccm-box {
            border-radius: 8px;
            padding: 16px 20px;
            margin: 24px 0;
            font-size: 15px;
            line-height: 1.6;
        }

        /* Blog Post: Share box (appears at the bottom) */
        .ccm-post-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            border-left: 5px solid #4c3a99;
        }
        .ccm-post-box strong {
            display: block;
            font-size: 17px;
            margin-bottom: 6px;
        }

        /* Static Page: Last updated notice (appears at the top) */
        .ccm-page-box {
            background: #f0f9ff;
            color: #1e3a5f;
            border-left: 5px solid #3b82f6;
        }
        .ccm-page-box .ccm-icon {
            margin-right: 6px;
        }

        /* Other post types: Subtle label */
        .ccm-default-box {
            background: #f8f8f8;
            color: #555555;
            border-left: 5px solid #cccccc;
            font-size: 13px;
        }
    </style>
    <?php
}

// ──────────────────────────────────────────────
// BLOCK B: Hook into the_content filter
// ──────────────────────────────────────────────

add_filter( 'the_content', 'ccm_modify_content' );

// ──────────────────────────────────────────────
// BLOCK C: The main content modifier function
// ──────────────────────────────────────────────

function ccm_modify_content( $content ) {
    // Only modify on single post/page views (not archives, feeds, etc.)
    if ( ! is_singular() ) {
        return $content;
    }

    // Detect the current post type
    $post_type = get_post_type();

    // Modify the content differently based on post type
    switch ( $post_type ) {

        case 'post':
            // ── Blog Posts: Append a "Share This Article" box at the bottom ──
            $share_box  = '<div class="ccm-box ccm-post-box">';
            $share_box .= '<strong>📢 Enjoyed this article?</strong>';
            $share_box .= 'Share it with your friends and help spread the word! ';
            $share_box .= 'You can also leave a comment below to let us know what you think.';
            $share_box .= '</div>';

            // Append: glue the box AFTER the original content
            $content = $content . $share_box;
            break;

        case 'page':
            // ── Static Pages: Prepend a "Last Updated" notice at the top ──
            $last_modified = get_the_modified_date( 'F j, Y' );

            $update_box  = '<div class="ccm-box ccm-page-box">';
            $update_box .= '<span class="ccm-icon">ℹ️</span>';
            $update_box .= '<strong>Last updated:</strong> ' . esc_html( $last_modified );
            $update_box .= '</div>';

            // Prepend: glue the box BEFORE the original content
            $content = $update_box . $content;
            break;

        default:
            // ── Any other post type (WooCommerce products, portfolios, etc.) ──
            $type_label  = '<div class="ccm-box ccm-default-box">';
            $type_label .= '📎 Content type: <strong>' . esc_html( $post_type ) . '</strong>';
            $type_label .= '</div>';

            // Prepend a small label
            $content = $type_label . $content;
            break;
    }

    return $content;
}
