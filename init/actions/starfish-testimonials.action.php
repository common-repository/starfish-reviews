<?php

use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Testimonials as SRM_TESTIMONIALS;
use Starfish\Twig as SRM_TWIG;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'init', 'srm_register_testimonial_cpt' );
add_action( 'add_meta_boxes_starfish_testimonial', 'srm_testimonial_meta_boxes' );
add_action( 'save_post_starfish_testimonial', 'srm_testimonial_save_meta_box_data' );

/**
 * Register a Collection post type.
 */
function srm_register_testimonial_cpt() {
	$notification_count = SRM_TESTIMONIALS::srm_get_total_testimonials( 'pending' );
	$labels             = array(
		'name'                     => _x( 'Testimonials', 'post type general name', 'starfish' ),
		'singular_name'            => _x( 'Testimonial', 'post type singular name', 'starfish' ),
		'menu_name'                => _x( 'Testimonials', 'admin menu', 'starfish' ),
		'name_admin_bar'           => _x( 'Testimonial', 'add new on admin bar', 'starfish' ),
		'add_new'                  => _x( 'Add New', 'Testimonial', 'starfish' ),
		'add_new_item'             => esc_html__( 'Add New Testimonial', 'starfish' ),
		'new_item'                 => esc_html__( 'New Testimonial', 'starfish' ),
		'edit_item'                => esc_html__( 'Edit Testimonial', 'starfish' ),
		'featured_image'           => esc_html__( 'Testimonial Image', 'starfish' ),
		'set_featured_image'       => esc_html__( 'Set Testimonial Image', 'starfish' ),
		'remove_featured_image'    => esc_html__( 'Remove Testimonial Image', 'starfish' ),
		'use_featured_image'       => esc_html__( 'Use as Testimonial Image', 'starfish' ),
		'view_item'                => esc_html__( 'View Testimonial', 'starfish' ),
		'all_items'                => $notification_count ? sprintf( _x( 'Testimonials', 'admin menu', 'starfish' ) . ' <span class="awaiting-mod">%d</span>', $notification_count ) : _x( 'Testimonials', 'admin menu', 'starfish' ),
		'search_items'             => esc_html__( 'Search Testimonials', 'starfish' ),
		'parent_item_colon'        => esc_html__( 'Parent Testimonials:', 'starfish' ),
		'not_found'                => esc_html__( 'No Testimonials found.', 'starfish' ),
		'not_found_in_trash'       => esc_html__( 'No Testimonials found in Trash.', 'starfish' ),
		'item_published'           => _x( 'Approved', 'post is approved and visible', 'starfish' ),
		'item_published_privately' => _x( 'Approved Privately', 'post approved privately', 'starfish' ),
		'item_scheduled'           => _x( 'Scheduled', 'post is scheduled to be approved', 'starfish' ),
	);

	$args = array(
		'labels'               => $labels,
		'description'          => esc_html__( 'Description.', 'starfish' ),
		'public'               => true,
		'publicly_queryable'   => true,
		'show_ui'              => true,
		'show_in_menu'         => 'starfish-parent',
		'query_var'            => true,
		'rewrite'              => array( 'slug' => 'testimonial' ),
		'capability_type'      => 'post',
		'capabilities'         => array( 'create_pages' => true ),
		'map_meta_cap'         => true,
		'has_archive'          => true,
		'hierarchical'         => false,
		'menu_position'        => 3,
		'supports'             => array(),
		'show_in_rest'         => true,
		'register_meta_box_cb' => 'srm_testimonial_meta_boxes',
		'taxonomies'           => array( 'category' ),
	);

	register_post_type( 'starfish_testimonial', $args );
	remove_post_type_support( 'starfish_testimonial', 'editor' );
}

/**
 * Add meta boxes for CPT Collection.
 *
 * @param object $post The post object.
 */
function srm_testimonial_meta_boxes( $post ) {
	// Normal.
	add_meta_box( 'starfish_testimonial_meta_box', esc_html__( 'Testimonial', 'starfish' ), 'srm_testimonial_build_meta_box', 'starfish_testimonial', 'normal', 'default' );
	// Side.
	add_meta_box( 'starfish_testimonial_toolbox_meta_box', esc_html__( 'Testimonial Toolbox', 'starfish' ), 'srm_testimonial_toolbox_build_meta_box', 'starfish_testimonial', 'side', 'default' );
	// Change Publish Meta Box Title
	global $wp_meta_boxes;
	$wp_meta_boxes['starfish_testimonial']['side']['core']['submitdiv']['title'] = 'Approve';
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
function srm_testimonial_build_meta_box( $post ) {
	$twig = SRM_TWIG::get_twig();
	wp_nonce_field( basename( __FILE__ ), 'testimonial_meta_box_nonce' );
	// Template Variables.
	$layout_variables = array(
		'referrer' => get_post_meta( $post->ID, SRM_META_TESTIMONIAL_REFERRER, true ),
		'rating'   => get_post_meta( $post->ID, SRM_META_TESTIMONIAL_RATING, true ),
		'name'     => get_post_meta( $post->ID, SRM_META_TESTIMONIAL_NAME, true ),
		'phone'    => get_post_meta( $post->ID, SRM_META_TESTIMONIAL_PHONE, true ),
		'email'    => get_post_meta( $post->ID, SRM_META_TESTIMONIAL_EMAIL, true ),
		'message'  => get_post_meta( $post->ID, SRM_META_TESTIMONIAL_MESSAGE, true ),
		'hidden'   => get_post_meta( $post->ID, SRM_META_TESTIMONIAL_HIDDEN, true ),
	);
	// Testimonial Content.
	echo $twig->render( 'starfish-testimonial-admin.html.twig', $layout_variables );
}

/**
 * Save Testimonial on submission.
 *
 * @param $post_id
 */
function srm_testimonial_save_meta_box_data( $post_id ) {
	// verify meta box nonce.
	if ( ! isset( $_POST['testimonial_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['testimonial_meta_box_nonce'] ) ), basename( __FILE__ ) ) ) {
		return;
	}
	// return if autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// store custom fields values.
	// ============================.
	update_post_meta( $post_id, SRM_META_TESTIMONIAL_REFERRER, sanitize_text_field( $_POST['srm-testimonial-referrer'] ) );
	update_post_meta( $post_id, SRM_META_TESTIMONIAL_RATING, sanitize_text_field( $_POST['srm-testimonial-rating'] ) );
	update_post_meta( $post_id, SRM_META_TESTIMONIAL_NAME, sanitize_text_field( $_POST['srm-testimonial-name'] ) );
	update_post_meta( $post_id, SRM_META_TESTIMONIAL_PHONE, sanitize_text_field( $_POST['srm-testimonial-phone'] ) );
	update_post_meta( $post_id, SRM_META_TESTIMONIAL_EMAIL, sanitize_text_field( $_POST['srm-testimonial-email'] ) );
	update_post_meta( $post_id, SRM_META_TESTIMONIAL_MESSAGE, sanitize_text_field( $_POST['srm-testimonial-message'] ) );
	update_post_meta( $post_id, SRM_META_TESTIMONIAL_HIDDEN, sanitize_text_field( $_POST['srm-testimonial-hidden'] ) );
}

/**
 * Toolbox functions (e.g. reset values to defaults).
 *
 * @param $post
 *
 * @throws Freemius_Exception
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_testimonial_toolbox_build_meta_box( $post ) {
	// Premium Only Styling.
	$premium_only      = SRM_FREEMIUS::starfish_premium_only_styles();
	$testimonial_usage = SRM_TESTIMONIALS::srm_get_testimonial_usage( get_the_ID() );
	$usage             = '<i>Not used yet.</i>';
	if ( ! empty( $testimonial_usage ) ) {
		$usage = '<ul>';
		$icon  = 'fa-file';
		foreach (SRM_TESTIMONIALS::srm_get_testimonial_usage( get_the_ID() ) as $post ) {
			if ( $post->post_type == 'post' ) {
				$icon = 'fa-thumbtack';
			}
			$usage .= '<li><i class="far ' . $icon . '" title="' . $post->post_type . '"></i> <a href="' . get_permalink( $post->ID ) . '" target="_blank" title="View" rel="noopener">' . $post->post_title . '</a> [ <a href="' .
				get_edit_post_link(
					$post->ID
				)
				. '" title="Edit" target="_blank" rel="noopener">Edit</a> ]</li>';
		}
		$usage .= '</ul>';
	}

	$twig = SRM_TWIG::get_twig();
	wp_nonce_field( basename( __FILE__ ), 'testimonial_meta_box_nonce' );
	// Template Variables.
	$variables = array(
		'premium_class'     => $premium_only['container_class'],
		'premium_badge'     => $premium_only['premium_only_badge'],
		'premium_link'      => $premium_only['premium_only_link'],
		'post_type'         => 'testimonial',
		'post_id'           => get_the_ID(),
		'embed_description' => __( 'Copy and Paste the below code snippet into any HTML webpage to show this testimony.', 'starfish' ),
		'embed_url'         => get_permalink(),
		'usage'             => $usage,
	);
	// Testimonial Content.
	echo $twig->render( 'starfish-admin-toolbox-meta.html.twig', $variables );

}

add_action( 'admin_footer-post.php', 'srm_approve_options' );
add_action( 'admin_footer-post-new.php', 'srm_approve_options' );

/**
 * Adjust publish status labels
 *
 * @return void
 */
function srm_approve_options() {
	global $post;

	if ( $post->post_type == 'starfish_testimonial' ) {

		$script = <<<SD

	       jQuery(document).ready(function($){
	           $('select[name=\"post_status\"] option[value=\"draft\"]').remove();
	           $('select[name=\"post_status\"] option[value=\"publish\"]').text('Approved');	      
	           if( "{$post->post_status}" == "publish" ){
	                $("span#post-status-display").html("Approved");
	           } else {
	                $("input#publish").val("Approve");
	           }          
	           $('.save-post-status').on('click', function(){
		            if(  $('select[name=\"post_status\"] option[value=\"publish\"]').is(':selected') ){
		                $("span#post-status-display").html("Approved");
		            } 
		            if(  $('select[name=\"post_status\"] option[value=\"pending\"]').is(':selected') ) {
		                $("span#post-status-display").html("Pending Review");
		            }
		       });
		       $('.edit-post-status').on('click', function(){
		            $('select[name=\"post_status\"] option[value=\"publish\"]').text('Approved');
		            if( $('select[name=\"post_status\"] option[value=\"publish\"]').is(':selected') ){
		                $("span#post-status-display").html("Approved");
		            }
		       });
		       $('.cancel-post-status').on('click', function(){
		            if( "{$post->post_status}" == "publish" ){
		                $("span#post-status-display").html("Approved");
		           } else {
		                $("input#publish").val("Approve");
		           }   
		       }); 
	      });
     
SD;

		echo '<script type="text/javascript">' . $script . '</script>';
	}
}

add_action(
	'admin_footer-edit.php',
	function() {
		global $post;
		if ( isset( $post->post_type ) && $post->post_type == 'starfish_testimonial' ) {
			echo "<script>
		    jQuery(document).ready( function($) {
				$('select[name=\"_status\"] option[value=\"publish\"]').text('Approved');
                $('select[name=\"_status\"] option[value=\"draft\"]').remove();
			});
	    </script>";
		}
	}
);
