<?php

use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use Starfish\Premium\ReviewsRs as SRM_REVIEWS_RS;

/**
 * Callbacks for Reviews Profile Ajax requests - Renew Profile
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0.0
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
function srm_reviews_profile_renew()
{

    if (! check_admin_referer('srm_review_profile', 'security')) {
        echo wp_json_encode(
            array(
                'success' => false,
                'message' => 'You do not have rights to perform this action',
            )
        );
        wp_die();
    } else {
        $profile_id          = (int) sanitize_text_field($_POST['profile_id']);
        $profile_data        = (array) SRM_REVIEWS_PROFILES::get_profile($profile_id);
        $rs_profile_response = SRM_REVIEWS_RS::add_profile($profile_data);
        $updated_profile_id  = false;
        // Set scrape status to "pending", awaiting callback from ReviewShake
        SRM_REVIEWS_PROFILES::update_scrape_status($profile_id, 'pending');
        // If ReviewShake was successful then update the profile in SRM.
        $updated_profile_id = SRM_REVIEWS_PROFILES::update_profile($profile_id, $profile_data);
        if (isset($rs_profile_response->success) && $rs_profile_response->success) {
            $job_id                  = $rs_profile_response->job_id;
            $profile_data['job_id']  = $job_id;
            $profile_data['active']  = 1;
            $updated_profile_id      = SRM_REVIEWS_PROFILES::update_profile($profile_id, $profile_data);
        }

        // Send the AJAX responses back to front-end.
        // ============================================.
        if (! $updated_profile_id) {
            echo wp_json_encode(
                array(
                    'success' => false,
                    'message' => 'Failed to renew the SRM Profile record,',
                )
            );
            wp_die();
        } else {
            echo wp_json_encode(
                array(
                    'success' => true,
                    'message' => 'Successfully renewed profile.',
                )
            );
            wp_die();
        }
    }
}

add_action('wp_ajax_srm-reviews-profile-renew', 'srm_reviews_profile_renew');
