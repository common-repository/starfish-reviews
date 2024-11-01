<?php

use Starfish\Premium\UtilsReviews as SRM_REVIEWS_UTILS;
use Starfish\Logging as SRM_LOGGING;

function srm_transient_import_reviews($transient, $value, $expiration)
{
    if (strpos($transient, 'srm-scrape-ready') !== false) {
        $srm_error_cya = '01';
        SRM_LOGGING::addEntry(
            array(
                'level'    => SRM_LOGGING::SRM_INFO,
                'action'  => 'Scrape Ready Transient Found',
                'message' => 'value=' . print_r($value, true),
                'code'    => 'SRM_A_ST_X_' . $srm_error_cya
            )
        );
        $profile_id    = $value['profile_id'];
        $job_id        = $value['job_id'];
        $total_reviews = intval($value['total_reviews']);
        $pages         = ceil($total_reviews / 100);
        $srm_error_cya = '02';
        SRM_LOGGING::addEntry(
            array(
                'level'    => SRM_LOGGING::SRM_INFO,
                'action'  => 'Importing Reviews',
                'message' => 'profile_id=' . $profile_id . ', job_id=' . $job_id . ', total_reviews=' . $total_reviews . ', pages=' . $pages,
                'code'    => 'SRM_A_ST_X_' . $srm_error_cya
            )
        );
        for ($page = 1; $page <= $pages; $page++) {
            SRM_REVIEWS_UTILS::import_reviews($profile_id, $job_id, $page);
        }
        SRM_LOGGING::addEntry(
            array(
                'level'    => SRM_LOGGING::SRM_INFO,
                'action'  => 'Importing Reviews Successful',
                'message' => 'profile_id=' . $profile_id . ', job_id=' . $job_id . ', total_reviews=' . $total_reviews . ', pages=' . $pages,
                'code'    => 'SRM_A_ST_X_' . $srm_error_cya
            )
        );
    }
}
add_action('setted_transient', 'srm_transient_import_reviews', 10, 3);
