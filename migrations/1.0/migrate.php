<?php

use Starfish\Freemius as SRM_FREEMIUS;

/**
 * Data Migration: Converting existing starfish_review custom post types to a new custom post type of starfish_feedback
 *
 * @version    1.0
 *
 * @package    WordPress
 * @subpackage starfish
 */

// Set globals.
global $wpdb, $wp_rewrite;

// Delete srm_review_slug option.
$review_slug = get_option( 'srm_review_slug' );
if ( false !== $review_slug ) {
	if ( ! delete_option( 'srm_review_slug' ) ) {
		throw new UnexpectedValueException( 'Failed to Delete Option: srm_review_slug' );
	} else {
		update_option( 'srm_feedback_slug', $review_slug );
	}
}

// Change email template {review-id} token to {tracking-id}.
$email_template_option = get_option( SRM_OPTION_EMAIL_TEMPLATE );
if ( false !== $email_template_option ) {
	$search_term = '{review-id}';
	$replace     = '{tracking-id}';
	if ( false !== strpos( $email_template_option, $search_term ) ) {
		$updated_template = str_replace( $search_term, $replace, $email_template_option );
		$updated_option   = update_option( SRM_OPTION_EMAIL_TEMPLATE, $updated_template );
		if ( strpos( get_option( SRM_OPTION_EMAIL_TEMPLATE ), $replace ) === false ) {
			throw new UnexpectedValueException( 'Failed to replace the token ' . $search_term . ' with ' . $replace . ' token  inside the Email Template ' );
		}
	}
}

// Update Review Prompt option for FREE versions
$review_prompt_option = get_option( SRM_OPTION_DEFAULT_REVIEW_PROMPT );
if ( false !== $review_prompt_option ) {
	$new_value = '<h3>Excellent! Please Rate us 5-stars</h3>...and leave a helpful review.';
	if ( SRM_FREEMIUS::starfish_fs()->is_free_plan() && $review_prompt_option !== $new_value ) {
		$updated_review_prompt_option = update_option( SRM_OPTION_DEFAULT_REVIEW_PROMPT, $new_value );
		if ( $new_value !== get_option( SRM_OPTION_DEFAULT_REVIEW_PROMPT ) ) {
			throw new UnexpectedValueException( 'Failed to update the Default Funnel Review Prompt' );
		}
	}
}

// Migrate existing starfish_review posts to starfish_feedback.
$old_post_type = 'starfish_review';
$new_post_type = 'starfish_feedback';
$old_guid      = 'starfish-review';
$new_guid      = 'starfish-feedback';
$current_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = '{$old_post_type}'" ); // no cache ok. db call ok.
if ( $current_count > 0 ) {
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->posts} SET post_type = REPLACE(post_type, %s, %s) 
                         WHERE post_type LIKE %s",
			$old_post_type,
			$new_post_type,
			$old_post_type
		)
	);
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET guid = REPLACE(guid, %s, %s)", "{$old_guid}", "{$new_guid}" ) ); // no cache ok. db call ok.
	wp_reset_postdata();
	$wp_rewrite->flush_rules();
	$new_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = '{$old_post_type}'" ); // no cache ok. db call ok.
	if ( $new_count > 0 ) {
		throw new UnexpectedValueException( 'Failed to update post types: ' . $new_count . ' Reviews still found.' );
	}
	$success_message = '<strong>Migrated ' . $current_count . ' Reviews</strong>';
}
