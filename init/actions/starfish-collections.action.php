<?php

use  Starfish\Freemius as SRM_FREEMIUS ;
use  Starfish\Funnels as SRM_FUNNELS ;
use  Starfish\Premium\Collections as SRM_COLLECTIONS ;
use  Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS ;
use  Starfish\Twig as SRM_TWIG ;
use  Twig\Error\LoaderError ;
use  Twig\Error\RuntimeError ;
use  Twig\Error\SyntaxError ;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Register a Collection post type.
 */
function srm_register_collection_cpt()
{
    $labels = array(
        'name'                  => _x( 'Collections', 'post type general name', 'starfish' ),
        'singular_name'         => _x( 'Collection', 'post type singular name', 'starfish' ),
        'menu_name'             => _x( 'Collections', 'admin menu', 'starfish' ),
        'name_admin_bar'        => _x( 'Collection', 'add new on admin bar', 'starfish' ),
        'add_new'               => _x( 'Add New', 'Collection', 'starfish' ),
        'add_new_item'          => esc_html__( 'Add New Collection', 'starfish' ),
        'new_item'              => esc_html__( 'New Collection', 'starfish' ),
        'edit_item'             => esc_html__( 'Edit Collection', 'starfish' ),
        'featured_image'        => esc_html__( 'Collection Image', 'starfish' ),
        'set_featured_image'    => esc_html__( 'Set Collection Image', 'starfish' ),
        'remove_featured_image' => esc_html__( 'Remove Collection Image', 'starfish' ),
        'use_featured_image'    => esc_html__( 'Use as Collection Image', 'starfish' ),
        'view_item'             => esc_html__( 'View Collection', 'starfish' ),
        'all_items'             => esc_html__( 'Collections', 'starfish' ),
        'search_items'          => esc_html__( 'Search Collections', 'starfish' ),
        'parent_item_colon'     => esc_html__( 'Parent Collections:', 'starfish' ),
        'not_found'             => esc_html__( 'No Collections found.', 'starfish' ),
        'not_found_in_trash'    => esc_html__( 'No Collections found in Trash.', 'starfish' ),
    );
    $args = array(
        'labels'               => $labels,
        'description'          => esc_html__( 'Description.', 'starfish' ),
        'public'               => true,
        'publicly_queryable'   => true,
        'show_ui'              => true,
        'show_in_menu'         => 'edit.php?post_type=starfish_feedback',
        'query_var'            => true,
        'rewrite'              => array(
        'slug' => 'collection',
    ),
        'capability_type'      => 'post',
        'capabilities'         => array(
        'create_pages' => true,
    ),
        'map_meta_cap'         => true,
        'has_archive'          => true,
        'hierarchical'         => false,
        'menu_position'        => 100,
        'supports'             => array( 'title', 'thumbnail' ),
        'show_in_rest'         => true,
        'register_meta_box_cb' => 'srm_collection_meta_boxes',
    );
    $args['show_in_menu'] = false;
    // Set Restrictions Based on Current Site's License/Plan.
    if ( !is_network_admin() && SRM_COLLECTIONS::srm_restrict_add_new() ) {
        $args['capabilities']['create_posts'] = false;
    }
    register_post_type( 'collection', $args );
}

/**
 * Add meta boxes for CPT Collection.
 *
 * @param object $post The post object.
 */
function srm_collection_meta_boxes( $post )
{
    // Normal.
    add_meta_box(
        'collection_preview_meta_box',
        esc_html__( 'Collection Preview', 'starfish' ),
        'srm_collection_preview_build_meta_box',
        'collection',
        'normal',
        'high'
    );
    add_meta_box(
        'collection_settings_meta_box',
        esc_html__( 'Collection Settings', 'starfish' ),
        'srm_collection_settings_build_meta_box',
        'collection',
        'normal',
        'default'
    );
    // Side.
    add_meta_box(
        'collection_toolbox_meta_box',
        esc_html__( 'Collection Toolbox', 'starfish' ),
        'srm_collection_toolbox_build_meta_box',
        'collection',
        'side',
        'default'
    );
}

/**
 * Build Meta Boxes for Collection.
 *
 * @param $post
 *
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_collection_settings_build_meta_box( $post )
{
    $twig = SRM_TWIG::get_twig();
    wp_nonce_field( basename( __FILE__ ), 'collection_settings_meta_box_nonce' );
    // Variables.
    $layout_variables = array(
        'plugin_url'  => SRM_PLUGIN_URL,
        'layout'      => get_post_meta( $post->ID, '_srm_collection_layout', true ),
        'columns'     => get_post_meta( $post->ID, '_srm_collection_columns', true ),
        'rows'        => get_post_meta( $post->ID, '_srm_collection_rows', true ),
        'slider'      => get_post_meta( $post->ID, '_srm_collection_slider', true ),
        'autoadvance' => get_post_meta( $post->ID, '_srm_collection_autoadvance', true ),
        'navigation'  => get_post_meta( $post->ID, '_srm_collection_navigation', true ),
    );
    $style_variables = array(
        'plugin_url'         => SRM_PLUGIN_URL,
        'color'              => get_post_meta( $post->ID, '_srm_collection_color', true ),
        'avatar_position'    => get_post_meta( $post->ID, '_srm_collection_avatar_position', true ),
        'avatar_size'        => get_post_meta( $post->ID, '_srm_collection_avatar_size', true ),
        'body_justification' => get_post_meta( $post->ID, '_srm_collection_body_justification', true ),
        'body_font'          => get_post_meta( $post->ID, '_srm_collection_body_font', true ),
        'body_font_size'     => get_post_meta( $post->ID, '_srm_collection_body_font_size', true ),
        'name_justification' => get_post_meta( $post->ID, '_srm_collection_name_justification', true ),
        'name_font'          => get_post_meta( $post->ID, '_srm_collection_name_font', true ),
        'name_font_size'     => get_post_meta( $post->ID, '_srm_collection_name_font_size', true ),
        'meta_justification' => get_post_meta( $post->ID, '_srm_collection_meta_justification', true ),
        'meta_font'          => get_post_meta( $post->ID, '_srm_collection_meta_font', true ),
        'meta_font_size'     => get_post_meta( $post->ID, '_srm_collection_meta_font_size', true ),
    );
    $elements_variables = array(
        'plugin_url'        => SRM_PLUGIN_URL,
        'elements_avatar'   => get_post_meta( $post->ID, '_srm_collection_elements_avatar', true ),
        'elements_title'    => get_post_meta( $post->ID, '_srm_collection_elements_title', true ),
        'elements_name'     => get_post_meta( $post->ID, '_srm_collection_elements_name', true ),
        'elements_date'     => get_post_meta( $post->ID, '_srm_collection_elements_date', true ),
        'elements_review'   => get_post_meta( $post->ID, '_srm_collection_elements_review', true ),
        'elements_rating'   => get_post_meta( $post->ID, '_srm_collection_elements_rating', true ),
        'elements_readmore' => get_post_meta( $post->ID, '_srm_collection_elements_readmore', true ),
        'elements_source'   => get_post_meta( $post->ID, '_srm_collection_elements_source', true ),
        'elements_noreview' => get_post_meta( $post->ID, '_srm_collection_elements_noreview', true ),
        'elements_maxchar'  => get_post_meta( $post->ID, '_srm_collection_elements_maxchar', true ),
    );
    $filter_variables = array(
        'plugin_url'        => SRM_PLUGIN_URL,
        'profiles'          => SRM_UTILS_REVIEWS::get_profiles(),
        'selected_profiles' => get_post_meta( $post->ID, '_srm_collection_filters_profiles', true ),
        'min_rating'        => get_post_meta( $post->ID, '_srm_collection_filters_minrating', true ),
        'max_reviews'       => get_post_meta( $post->ID, '_srm_collection_filters_maxreviews', true ),
        'age'               => get_post_meta( $post->ID, '_srm_collection_filters_age', true ),
        'start_date'        => get_post_meta( $post->ID, '_srm_collection_filters_startdate', true ),
        'end_date'          => get_post_meta( $post->ID, '_srm_collection_filters_enddate', true ),
    );
    ?>
    <ul id="srm-collection-settings-nav" class="nav nav-pills mt-3 mb-3" role="tablist"">
    <li class="nav-item">
        <a class="nav-link active" id="filter-pill" data-toggle="pill" href="#filter" role="tab" aria-controls="filter"
           aria-selected="true">Filters</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="layout-pill" data-toggle="pill" href="#layout" role="tab" aria-controls="layout"
           aria-selected="false">Layout</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="elements-pill" data-toggle="pill" href="#elements" role="tab" aria-controls="elements"
           aria-selected="false">Elements</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="style-pill" data-toggle="pill" href="#style" role="tab" aria-controls="style"
           aria-selected="false">Style</a>
    </li>
    </ul>
    <div class="tab-content mt-3 mb-3" id="srm-collection-settings-content">
        <?php 
    // Layout Settings
    echo  $twig->render( 'starfish-collection-admin-layout.html.twig', $layout_variables ) ;
    // Elements Settings
    echo  $twig->render( 'starfish-collection-admin-elements.html.twig', $elements_variables ) ;
    // Style Settings
    echo  $twig->render( 'starfish-collection-admin-style.html.twig', $style_variables ) ;
    // Review Filters
    echo  $twig->render( 'starfish-collection-admin-filter.html.twig', $filter_variables ) ;
    ?>
    </div>
    <?php 
}

/**
 * @param object $post The post object.
 *
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_collection_preview_build_meta_box( $post )
{
    $twig = SRM_TWIG::get_twig();
    $preview_variables = SRM_COLLECTIONS::srm_get_collection_variables( $post->ID );
    echo  $twig->render( 'starfish-collection.html.twig', $preview_variables ) ;
}

/**
 * Save Collection Settings on submission.
 *
 * @param $post_id
 */
function srm_collection_settings_save_meta_box_data( $post_id )
{
    // verify meta box nonce.
    if ( !isset( $_POST['collection_settings_meta_box_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['collection_settings_meta_box_nonce'] ) ), basename( __FILE__ ) ) ) {
        return;
    }
    // return if autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check the user's permissions.
    if ( !current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    // store custom fields values
    // ============================
    // GENERAL
    
    if ( array_key_exists( 'srm-collection-hidden', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_hidden', sanitize_text_field( $_POST['srm-collection-hidden'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_hidden', sanitize_text_field( '0' ) );
    }
    
    // LAYOUT.
    if ( array_key_exists( 'srm-collection-layout', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_layout', sanitize_text_field( $_POST['srm-collection-layout'] ) );
    }
    if ( array_key_exists( 'srm-collection-columns', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_columns', sanitize_text_field( $_POST['srm-collection-columns'] ) );
    }
    if ( array_key_exists( 'srm-collection-rows', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_rows', sanitize_text_field( $_POST['srm-collection-rows'] ) );
    }
    if ( array_key_exists( 'srm-collection-slider', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_slider', sanitize_text_field( $_POST['srm-collection-slider'] ) );
    }
    if ( array_key_exists( 'srm-collection-autoadvance', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_autoadvance', sanitize_text_field( $_POST['srm-collection-autoadvance'] ) );
    }
    if ( array_key_exists( 'srm-collection-navigation', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_navigation', sanitize_text_field( $_POST['srm-collection-navigation'] ) );
    }
    // STYLE.
    if ( array_key_exists( 'srm-collection-color', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_color', sanitize_text_field( $_POST['srm-collection-color'] ) );
    }
    if ( array_key_exists( 'srm-collection-avatar-position', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_avatar_position', sanitize_text_field( $_POST['srm-collection-avatar-position'] ) );
    }
    if ( array_key_exists( 'srm-collection-avatar-size', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_avatar_size', sanitize_text_field( $_POST['srm-collection-avatar-size'] ) );
    }
    if ( array_key_exists( 'srm-collection-body-justification', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_body_justification', sanitize_text_field( $_POST['srm-collection-body-justification'] ) );
    }
    if ( array_key_exists( 'srm-collection-body-font', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_body_font', sanitize_text_field( $_POST['srm-collection-body-font'] ) );
    }
    if ( array_key_exists( 'srm-collection-body-font-size', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_body_font_size', sanitize_text_field( $_POST['srm-collection-body-font-size'] ) );
    }
    if ( array_key_exists( 'srm-collection-name-justification', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_name_justification', sanitize_text_field( $_POST['srm-collection-name-justification'] ) );
    }
    if ( array_key_exists( 'srm-collection-name-font', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_name_font', sanitize_text_field( $_POST['srm-collection-name-font'] ) );
    }
    if ( array_key_exists( 'srm-collection-name-font-size', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_name_font_size', sanitize_text_field( $_POST['srm-collection-name-font-size'] ) );
    }
    if ( array_key_exists( 'srm-collection-meta-justification', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_meta_justification', sanitize_text_field( $_POST['srm-collection-meta-justification'] ) );
    }
    if ( array_key_exists( 'srm-collection-meta-font', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_meta_font', sanitize_text_field( $_POST['srm-collection-meta-font'] ) );
    }
    if ( array_key_exists( 'srm-collection-meta-font-size', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_meta_font_size', sanitize_text_field( $_POST['srm-collection-meta-font-size'] ) );
    }
    // ELEMENTS.
    
    if ( array_key_exists( 'srm-collection-elements-title', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_title', sanitize_text_field( $_POST['srm-collection-elements-title'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_title', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-name', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_name', sanitize_text_field( $_POST['srm-collection-elements-name'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_name', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-date', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_date', sanitize_text_field( $_POST['srm-collection-elements-date'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_date', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-review', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_review', sanitize_text_field( $_POST['srm-collection-elements-review'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_review', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-rating', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_rating', sanitize_text_field( $_POST['srm-collection-elements-rating'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_rating', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-avatar', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_avatar', sanitize_text_field( $_POST['srm-collection-elements-avatar'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_avatar', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-readmore', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_readmore', sanitize_text_field( $_POST['srm-collection-elements-readmore'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_readmore', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-source', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_source', sanitize_text_field( $_POST['srm-collection-elements-source'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_source', sanitize_text_field( '0' ) );
    }
    
    
    if ( array_key_exists( 'srm-collection-elements-noreview', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_noreview', sanitize_text_field( $_POST['srm-collection-elements-noreview'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_elements_noreview', sanitize_text_field( '0' ) );
    }
    
    if ( array_key_exists( 'srm-collection-elements-maxchar', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_elements_maxchar', sanitize_text_field( $_POST['srm-collection-elements-maxchar'] ) );
    }
    // Filters.
    if ( array_key_exists( 'srm-collection-filters-profiles', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_filters_profiles', $_POST['srm-collection-filters-profiles'] );
    }
    if ( array_key_exists( 'srm-collection-filters-minrating', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_filters_minrating', sanitize_text_field( $_POST['srm-collection-filters-minrating'] ) );
    }
    
    if ( array_key_exists( 'srm-collection-filters-maxreviews', $_POST ) && !empty($_POST['srm-collection-filters-maxreviews']) ) {
        update_post_meta( $post_id, '_srm_collection_filters_maxreviews', sanitize_text_field( $_POST['srm-collection-filters-maxreviews'] ) );
    } else {
        update_post_meta( $post_id, '_srm_collection_filters_maxreviews', sanitize_text_field( '0' ) );
    }
    
    if ( array_key_exists( 'srm-collection-filters-age', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_filters_age', sanitize_text_field( $_POST['srm-collection-filters-age'] ) );
    }
    if ( array_key_exists( 'srm-collection-filters-startdate', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_filters_startdate', sanitize_text_field( $_POST['srm-collection-filters-startdate'] ) );
    }
    if ( array_key_exists( 'srm-collection-filters-enddate', $_POST ) ) {
        update_post_meta( $post_id, '_srm_collection_filters_enddate', sanitize_text_field( $_POST['srm-collection-filters-enddate'] ) );
    }
}

/**
 * Toolbox functions (e.g. reset values to defaults).
 *
 * @param $post
 *
 * @throws Freemius_Exception
 */
function srm_collection_toolbox_build_meta_box( $post )
{
    // Premium Only Styling
    $premium_only = SRM_FREEMIUS::starfish_premium_only_styles();
    $premium_container = $premium_only['container_class'];
    $premium_badge = $premium_only['premium_only_badge'];
    $premium_link = $premium_only['premium_only_link'];
    $toolbox_collection_id = get_the_ID();
    $toolbox_embed_description = __( 'Copy and Paste the below code snippet into any HTML webpage to show this funnel.', 'starfish' );
    $toolbox_embed_url = get_permalink();
    $collection_usage = SRM_COLLECTIONS::get_collection_usage( $toolbox_collection_id );
    $usage = "<i>Not used yet.</i>";
    
    if ( !empty($collection_usage) ) {
        $usage = '<ul>';
        $icon = 'fa-file';
        foreach ( SRM_COLLECTIONS::get_collection_usage( $toolbox_collection_id ) as $post ) {
            if ( $post->post_type == 'post' ) {
                $icon = 'fa-thumbtack';
            }
            $usage .= '<li><i class="far ' . $icon . '" title="' . $post->post_type . '"></i> <a href="' . get_permalink( $post->ID ) . '" target="_blank" title="View" rel="noopener">' . $post->post_title . '</a> [ <a href="' . get_edit_post_link( $post->ID ) . '" title="Edit" target="_blank" rel="noopener">Edit</a> ]</li>';
        }
        $usage .= '</ul>';
    }
    
    print <<<TOOLS
    <div id="srm-collection-shortcode" class="srm-collection-tool {$premium_container}">
        <script>new ClipboardJS('#srm-copy-shortcode-link-{$toolbox_collection_id}');</script>
        <div class="srm-sidebar-subtitle">
           {$premium_badge} Shortcode {$premium_link}
        </div>
        <code>[starfish collection="{$toolbox_collection_id}"]</code>
        <a id="srm-copy-shortcode-link-{$toolbox_collection_id}" href="javascript:;" title="Copy to Clipboard" data-clipboard-text='[starfish collection="{$toolbox_collection_id}"]'>
            <i class="fa fa-copy"></i>
        </a>
    </div>
    <div id="srm-collection-embed-snippet" class="srm-collection-tool">
        <div class="srm-sidebar-subtitle">Embed Code</div>
        <p class="description">{$toolbox_embed_description}</p>
        <script>new ClipboardJS('#srm-copy-embedcode-link-{$toolbox_collection_id}');</script>
        <a id="srm-copy-embedcode-link-{$toolbox_collection_id}" href="javascript:;" title="Copy to Clipboard" data-clipboard-text='&lt;object 
        name="starfish-reviews-collection-{$toolbox_collection_id}" type="text/html" style="overflow: hidden;" align="center" width="640" height="425" data="{$toolbox_embed_url}"&gt;&lt;/object&gt;'>
            <i class="fa fa-copy"></i>
        </a>
        <pre>
            <code class="html">
&lt;object name="starfish-reviews-collection-{$toolbox_collection_id}" type="text/html" style="overflow: hidden;" align="center" width="640" height="425" data="{$toolbox_embed_url}"&gt;&lt;/object&gt;
            </code>
        </pre>
    </div>
    <div id="srm-collection-usage" class="srm-collection-tool">
        <div class="srm-sidebar-subtitle">Collection Usage</div>
        {$usage}
    </div>
TOOLS;
}
