<?php

use Starfish\Logging as SRM_LOGGING;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Migrate Rich Reviews records to Starfish Reviews Testimonials
 *
 * @package    WordPress
 * @subpackage starfish
 */
function srm_richreviews_migrate()
{
    global $wpdb;

    // Get All Rich Reviews review records.
    $get_richreviews_sql = "SELECT `date_time`,`reviewer_name`,`reviewer_email`,`review_title`,`review_rating`,`review_text`,`review_category`,`review_status`,`post_id` FROM {$wpdb->prefix}richreviews";
    $richreviews = $wpdb->get_results($get_richreviews_sql, 'OBJECT'); // db call ok; no-cache ok.

    SRM_LOGGING::addEntry(
        array(
            'level'   => SRM_LOGGING::SRM_INFO,
            'action'  => 'Rich Reviews to Migrate',
            'message' => print_r($richreviews, true),
            'code'    => 'SRM_A_RRM_X_01',
        )
    );

    $total_reviews = count($richreviews);
    $completed = 0;
    // Create a new Testimonial for each Rich Reviews records.
    foreach ($richreviews as $rich) {
        // Create new Testimonial category.
        $category = $rich->review_category;
        $category_id = wp_create_category($category);

        // Set post title (date and time).
        $date = date('j M Y g:i a', strtotime($rich->date_time));
        $date_gmt = get_gmt_from_date($date);
        $post_title = $date;

        // Set post status.
        if (filter_var($rich->review_status, FILTER_VALIDATE_BOOLEAN)) {
            $post_status = 'publish';
        } else {
            $post_status = 'pending';
        }

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Rich Review Migration - Migrating Record Index: ' . $completed,
                'message' => 'CategoryID: ' . $category_id . ', Post Title: ' . $post_title . ', Post Status: ' . $post_status . ' ('.$rich->review_status.')',
                'code'    => 'SRM_A_RRM_X_02',
            )
        );

        // Create new Post.
        // Check if testimonial already exists by reviewer name.
        $existing = $wpdb->get_results("select * from $wpdb->postmeta where meta_key = '_srm_testimonial_name' and meta_value = '{$rich->reviewer_name}'");

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Rich Review Already Migrated? - Record Index: ' . $completed,
                'message' => print_r($existing, true),
                'code'    => 'SRM_A_RRM_X_03',
            )
        );

        if(empty($existing)) {

            $new_testimonial = [
                'post_type'			=> 'starfish_testimonial',
                'post_title'    	=> wp_strip_all_tags($post_title),
                'post_name'			=> str_replace([' ',':'], '-', strtolower($post_title)),
                'post_status'   	=> $post_status,
                'post_category' 	=> [$category_id],
                'post_date_gmt' 	=> $date_gmt,
                'post_modified_gmt' => $date_gmt
            ];
            $testimonial_id = wp_insert_post($new_testimonial, false, false);
            if ($testimonial_id) {
                // Add custom fields for new post
                add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_REFERRER, get_permalink($rich->post_id));
                add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_RATING, $rich->review_rating);
                add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_NAME, $rich->reviewer_name);
                add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_EMAIL, $rich->reviewer_email);
                add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_MESSAGE, $rich->review_text);
            }
        }
        $completed += 1;
        $progress_transient = ['completed' => $completed, 'total' => $total_reviews, 'percentage' => (ceil($completed / $total_reviews) * 100)];
        // Update migration transient
        set_transient(SRM_TRAN_RICHREVIEWS_MIGRATION, $progress_transient, 7200);  // Two-hour expiration
    }
    update_option(SRM_OPTION_RICHREVIEWS_MIGRATED, true);
    echo wp_json_encode(['type' => 'success', 'content' => 'processing']);
    wp_die();
}
add_action('wp_ajax_starfish-execute-richreviews-migrations', 'srm_richreviews_migrate');
