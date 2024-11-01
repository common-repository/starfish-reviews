<?php

use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;
use Starfish\Premium\ReviewsRs as SRM_REVIEWS_RS;
use Starfish\Logging as SRM_LOGGING;

/**
 * Callbacks for Reviews Profile Ajax requests
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    1.0.0
 * @link       https://api.supervisor.reviewshake.com
 * @see        Reviews, ReviewsProfiles
 * @since      Release: 3.0.0
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Process submitted Reviews Profile
 **/
function srm_create_reviews_profile()
{

    if (
        ! check_admin_referer(
            'srm_review_profile',
            'security'
        )
    ) {
        echo wp_json_encode(
            array(
                'success' => false,
                'message' => 'You do not have rights to perform this action',
            )
        );
        wp_die();
    } else {
        $srm_error_cya = "00";
        $form_data = isset($_POST['data']) ? wp_unslash($_POST['data']) : array();
        parse_str($form_data, $data);
        $profile_id      = (int) sanitize_text_field($data['srm-reviews-profile-id']);
        $job_id          = sanitize_text_field($data['srm-reviews-profile-job-id']);
        $name            = sanitize_text_field($data['srm-reviews-profile-name']);
        $url             = esc_url_raw($data['srm-reviews-profile-site-url']);
        $query           = sanitize_text_field($data['srm-reviews-profile-query']);
        $place_id        = sanitize_text_field($data['srm-reviews-profile-placeid']);
        $active          = sanitize_text_field($data['srm-reviews-profile-active']);
        $scrape_diff     = sanitize_text_field($data['srm-reviews-profile-diff-last']);
        $scrape_now      = sanitize_text_field($data['srm-reviews-profile-scrape-now']);
        $profile_payload = array(
            'name'     => $name,
            'url'      => $url,
            'query'    => $query,
            'place_id' => $place_id,
            'active'   => $active,
            'job_id'   => $job_id,
            'hide'     => '0', // Default value is false.
        );

        if ($profile_id === null || $profile_id === 0) {
            $srm_error_cya = "01";
            // Make sure there is not already a profile with the same information
            // (i.e. URL, Place ID, or Query).
            global $wpdb;
            $error_message_template = 'A profile with the same %s already exists.';
            $query                  = "SELECT `id` FROM {$wpdb->prefix}srm_reviews_profiles WHERE";
            if (! empty($profile_payload['url']) && false === strpos($profile_payload['url'], 'google')) {
                $query         = $query . " `url` = '%s'";
                $error_message = sprintf($error_message_template, 'URL');
            } elseif (! empty($profile_payload['query'])) {
                $query         = $query . " `query` = '%s'";
                $error_message = sprintf($error_message_template, 'Query');
            } elseif (! empty($profile_payload['place_id'])) {
                $query         = $query . " `place_id` = '%s'";
                $error_message = sprintf($error_message_template, 'Place ID');
            }
            $wpdb->get_results(
                $wpdb->prepare(
                    $query,
                    $url,
                    $query,
                    $place_id
                ),
                'ARRAY'
            ); // db call ok; no-cache ok.
            if (! empty($wpdb->last_result[0]->id)) {
                SRM_LOGGING::addEntry(array(
                    'level'   => SRM_LOGGING::SRM_ERROR,
                    'action'  => 'Create New Profile',
                    'message' => 'error=' . $error_message . ',
                                    profile_payload=' . print_r($profile_payload, true),
                    'code'    => 'SRM_A_CRP_X_' . $srm_error_cya
                ));
                echo wp_json_encode(
                    array(
                        'success' => false,
                        'message' => $error_message,
                    )
                );
                wp_die();
            }
            $profile_id = SRM_REVIEWS_PROFILES::add_profile($profile_payload);
        } else {
            $srm_error_cya = "02";
            $profile_id = SRM_REVIEWS_PROFILES::update_profile($profile_id, $profile_payload);
        }
        // Create the profile at ReviewShake to initiate the scrape
        // ======================================================.
        if (! empty($scrape_now)) {
            $srm_error_cya = "03";
            // If the option to renew does NOT include the last-scrape (diff) job,
            // then clear the current Job ID from being included in the ReviewShake payload
            if (empty($scrape_diff)) {
                // Set the Diff Job ID to null to trigger a fresh scrape
                $profile_payload[ 'job_id' ] = null;
            }
            $rs_profile_response = SRM_REVIEWS_RS::add_profile($profile_payload);
            $job_id              = $rs_profile_response->job_id;
            if (isset($rs_profile_response->success) && $rs_profile_response->success) {
                // Set Summary with Pending Status; awaiting ReviewShake Callback
                $summary_data[ 'crawl_status' ] = 'pending';
                $summary_data[ 'last_crawl' ]   = date('Y-m-d H:i:s');
                $profile_payload[ 'job_id' ]    = $job_id;
                $profile_id                     = SRM_REVIEWS_PROFILES::update_profile($profile_id, $profile_payload);
            } else {
                $summary_data[ 'crawl_status' ] = 'failed';
                $summary_data[ 'last_crawl' ]   = date('Y-m-d H:i:s');
            }
            $summary_data[ 'profile_id' ] = $profile_id;
            $summary_id                   = SRM_UTILS_REVIEWS::update_profile_summary($summary_data);
            if (! $summary_id) {
                echo wp_json_encode(
                    array(
                        'success' => false,
                        'message' => 'Failed to update review summary',
                    )
                );
                wp_die();
            }
        }

        // Send the AJAX responses back to front-end.
        // ============================================.
        if (! $profile_id) {
            SRM_LOGGING::addEntry(
                array(
                    'level'   =>  SRM_LOGGING::SRM_ERROR,
                    'action'  => 'Create New Profile',
                    'message' => 'profile_id=' . print_r($profile_id) . ', 
                                  summary_data=' . print_r($summary_data) . ', 
                                  profile_payload=' . print_r($profile_payload) . ', 
                                  rs_response=' . print_r($rs_profile_response),
                    'code'    => 'SRM_A_CRP_X_' . $srm_error_cya
                )
            );
            echo wp_json_encode(
                array(
                    'success' => false,
                    'message' => 'Failed to create the new SRM Profile record (Error Code: SRM_A_CRP_X_' . $srm_error_cya . ')',
                )
            );
            wp_die();
        } elseif (is_string($profile_id) && $profile_id === 'exists') {
            echo wp_json_encode(
                array(
                    'success' => false,
                    'message' => 'Profile already exists. (ID: ' . $profile_id . ')',
                )
            );
            wp_die();
        } else {
            echo wp_json_encode(
                array(
                    'success' => true,
                    'message' => 'Successfully created profile and started first Scrape.',
                    'id'      => $profile_id
                )
            );
            wp_die();
        }
    }
}

add_action('wp_ajax_srm-create-reviews-profile', 'srm_create_reviews_profile');

function srm_reviews_profile_hide()
{
    $is_hidden  = rest_sanitize_boolean($_POST['is_hidden']);
    $profile_id = sanitize_text_field($_POST['profile_id']);
    if (
        ! check_admin_referer(
            'srm_review_profile',
            'security'
        )
    ) {
        echo wp_json_encode(
            array(
                'success' => false,
                'message' => 'You do not have rights to perform this action',
            )
        );
        wp_die();
    } else {
        if (SRM_REVIEWS_PROFILES::update_profile_hide($profile_id, $is_hidden) !== 0) {
            echo wp_json_encode(
                array(
                    'success' => true,
                    'message' => 'Successfully Updated Profile Hidden flag',
                )
            );
            wp_die();
        } else {
            echo wp_json_encode(
                array(
                    'success' => false,
                    'message' => 'Failed to update Profile Hidden flag',
                )
            );
            wp_die();
        }
    }
}

add_action('wp_ajax_srm-reviews-profile-hide', 'srm_reviews_profile_hide');

function srm_check_reviews_profile_url()
{
    if (
        ! check_admin_referer(
            'srm_review_profile',
            'security'
        )
    ) {
        echo wp_json_encode(
            array(
                'success' => false,
                'message' => 'You do not have rights to perform this action',
            )
        );
        wp_die();
    } else {
        $srm_error_cya      = "01";
        $profile_url        = sanitize_text_field($_POST['profile_url']);
        $review_sites       = SRM_UTILS_REVIEWS::get_review_sites();
        $profile_domain     = parse_url($profile_url, PHP_URL_HOST);
        $authorized_domains = array();
        foreach ($review_sites as $site) {
            $authorized_domains[] = parse_url($site->url, PHP_URL_HOST);
        }
        // Confirm the URL's domain is one from the authorized lists
        if (!in_array($profile_domain, $authorized_domains)) {
            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_ERROR,
                    'action'  => "Profile URL is not authorized",
                    'message' => "url=" . print_r($profile_url, true),
                    'code'    => 'SRM_A_CRPU_X_' . $srm_error_cya
                )
            );
            $status = false;
            $message = 'The Profile URL domain/host does not match the authorized list of domains';
            echo wp_json_encode(
                array(
                    'success' => $status,
                    'message' => $message,
                )
            );
            wp_die();
        } else {
            $srm_error_cya = "02";
            // Check the URL
            $ch = curl_init($profile_url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CULROPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch) == 0 && $http == 200) {
                SRM_LOGGING::addEntry(
                    array(
                        'level' => SRM_LOGGING::SRM_INFO,
                        'action' => "Does Profile URL ({$profile_url}) Exist",
                        'message' => "http_code=" . print_r($http, true),
                        'code' => 'SRM_A_CRPU_X_' . $srm_error_cya
                    )
                );
                $status = true;
                $message = "The Profile URL successfully returned a 200";
            } else {
                SRM_LOGGING::addEntry(
                    array(
                        'level' => SRM_LOGGING::SRM_ERROR,
                        'action' => "Does Profile URL ({$profile_url}) Exist",
                        'message' => "http_code=" . print_r($http, true),
                        'code' => 'SRM_A_CRPU_X_' . $srm_error_cya
                    )
                );
                $status = false;
                $message = 'The Review URL did not return successfully, please ensure the "Review URL Preview" above is correct.';
            }
            curl_close($ch);

            echo wp_json_encode(
                array(
                    'success' => $status,
                    'message' => $message,
                )
            );
            wp_die();
        }
    }
}

add_action('wp_ajax_srm-reviews-profile-validate-url', 'srm_check_reviews_profile_url');
