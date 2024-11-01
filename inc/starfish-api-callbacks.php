<?php

use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;
use Starfish\Logging as SRM_LOGGING;

/**
 * CALLBACK for ReviewShake to update results of new profile and scrape reviews
 *
 * @param array $payload The summary information of a profile
 *
 * @return int|false summary ID or false if failed.
 */
function srm_profile_callback($payload)
{
    // Convert data to summary object
    $summary_data['review_count']        = $payload->review_count;
    $summary_data['average_rating']      = $payload->average_rating;
    $summary_data['last_crawl']          = $payload->last_crawl;
    $summary_data['crawl_status']        = $payload->crawl_status;
    $summary_data['percentage_complete'] = $payload->percentage_complete;
    $summary_data['result_count']        = $payload->result_count;
    $summary_data['profile_id']          = SRM_UTILS_REVIEWS::get_profile_id($payload->job_id);
    // Update profile to Active
    $srm_error_cya = "01";
    SRM_UTILS_REVIEWS::update_profile_status($summary_data['profile_id'], SRM_UTILS_REVIEWS::ACTIVE);
    SRM_LOGGING::addEntry(
        array(
            'level'    => SRM_LOGGING::SRM_INFO,
            'action'  => 'Profile Scrape Callback: Update Profile Status',
            'message' => print_r($summary_data, true),
            'code'    => 'SRM_A_ACB_X_' . $srm_error_cya
        )
    );
    // Update Profile Summary
    $srm_error_cya = "02";
    $result = SRM_UTILS_REVIEWS::update_profile_summary($summary_data);
    SRM_LOGGING::addEntry(
        array(
            'level'    => SRM_LOGGING::SRM_INFO,
            'action'  => 'Profile Scrape Callback: Updated Profile Summary',
            'message' => 'summary_id=' . print_r($result, true),
            'code'    => 'SRM_A_ACB_X_' . $srm_error_cya
        )
    );
    // Set transient to signify reviews are ready to be retrieved
    if ('complete' === $summary_data['crawl_status'] && ! empty($summary_data['profile_id']) && $summary_data['result_count'] > 0) {
        $srm_error_cya = "03";
        SRM_LOGGING::addEntry(
            array(
                'level'    => SRM_LOGGING::SRM_INFO,
                'action'  => 'Profile Scrape Callback: Ready to Import Reviews [ profile_id=' . $summary_data['profile_id'] . ']',
                'message' => 'summary_id=' . print_r($result, true),
                'code'    => 'SRM_A_ACB_X_' . $srm_error_cya
            )
        );
        set_transient('srm-scrape-ready', array( "profile_id" => $summary_data['profile_id'], "job_id" => $payload->job_id, "total_reviews" => $payload->result_count ), DAY_IN_SECONDS);
    }
    return $result;
}

/**
 * CALLBACK for Starfish Reviews
 *
 * @return array Array of reviews
 */
function srm_reviews_callback()
{
    // Retrieve all Reviews from database
    global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}srm_reviews";

    return $wpdb->get_results($sql, 'ARRAY_A');
}

/**
 * CALLBACK for Starfish Reviews Last Summary
 *
 * @return array Array of reviews
 */
function srm_reviews_summary_callback()
{
    // Retrieve all Reviews Last Summary from database
    global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}srm_reviews_last_summary";

    return $wpdb->get_results($sql, 'ARRAY_A');
}
