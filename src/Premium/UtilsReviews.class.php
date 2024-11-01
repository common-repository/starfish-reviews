<?php

namespace Starfish\Premium;

use Starfish\Premium\ReviewsRs as SRM_REVIEWS_RS;
use Starfish\Premium\ReviewSites as SRM_REVIEW_SITES;
use const SRM_UPLOADS_DIR;

/**
 * Utilities for using scraping and handling reviews
 * outside the reviews and profiles admin pages
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0.0
 * @link       https://api.supervisor.reviewshake.com
 * @see        ReviewsRs, ReviewsProfiles, Reviews
 * @since      Release: 2.4.0
 */
class UtilsReviews {

	const EXPIRED  = 'Expired';
	const ACTIVE   = 'Active';
	const DISABLED = 'Disabled';

	/**
	 * Retrieve Profile ID by Job ID
	 *
	 * @param String $job_id Profile Job ID
	 *
	 * @return Array $profile_id Profile ID
	 */
	public static function get_profile_id( $job_id ) {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}srm_view_reviews_profiles WHERE job_id = %d", $job_id ), 'ARRAY_A' ); // db call ok; no-cache ok.

		return $result[0]['id'];
	}

    /**
     * Retrieve All profiles
     * Returns only the profile's name, ID, and Created date
     *
     * @param Boolean|null $active
     * @return Array $profiles
     */
	public static function get_profiles(string $active = null) {
		global $wpdb;
        $where = null;
        if(!empty($active)) {
            $where = ' WHERE active = ' . $active;
        }
		return $wpdb->get_results( "SELECT id, name, created FROM {$wpdb->prefix}srm_reviews_profiles {$where}", 'ARRAY_A' ); // db call ok; no-cache ok.
	}

	/**
	 * Update the profile's Last Summary
	 *
	 * @param array $summary_data The summary data to update
	 *
	 * @return int|false The Summary ID of the updated summary, otherwise false
	 */
	public static function update_profile_summary( $summary_data ) {
		if ( empty( $summary_data ) ) {
			return false;
		}

		global $wpdb;
		$summary_id = false;
		// check if a summary already exists or not, if not create.
		$profile_id = $summary_data['profile_id'];
		$get_sql    = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}srm_reviews_last_summary WHERE profile_id = %d", $profile_id );
		$existing   = $wpdb->get_results( $get_sql, 'ARRAY_A' ); // db call ok; no-cache ok.
		if ( ! empty($existing) && (int) $existing[0]['profile_id'] === (int) $profile_id ) {
			$wpdb->update( $wpdb->prefix . 'srm_reviews_last_summary', $summary_data, array( 'profile_id' => $profile_id ) ); // db call ok; no-cache ok.
			$result     = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}srm_reviews_last_summary WHERE profile_id = %d", $profile_id ), 'ARRAY_A' ); // db call ok; no-cache ok.
			$summary_id = $result[0]['id'];
		} else {
			$summary_id = self::create_profile_summary( $summary_data );
		}

		return $summary_id;

	}

	/**
	 * Update the profile's Last Summary
	 *
	 * @param array $summary_data The summary data to update.
	 *
	 * @return int|false The Profile ID of the updated summary, otherwise false
	 */
	public static function create_profile_summary( $summary_data = null ) {
		if ( empty( $summary_data ) ) {
			return false;
		}

		global $wpdb;
		$result = $wpdb->insert( $wpdb->prefix . 'srm_reviews_last_summary', $summary_data ); // db call ok; no-cache ok.
		if ( ! $result ) {
			$summary_id = false;
		} else {
			$summary_id = $wpdb->insert_id;
		}

		return $summary_id;
	}

	/**
	 * Scrape for new reviews after submitting a new or update Profile Details
	 *
	 * @param string $profile_id The Profile ID for the requested reviews.
	 * @param string $job_id The job ID assigned to the profile.
	 *
	 * @return int|false
	 */
	public static function scrape_reviews_callback( $profile_id, $job_id ) {
		$summary_id          = false;
		$rs_reviews_response = SRM_REVIEWS_RS::get_reviews( $job_id );
		// Insert the reviews and update the last summary.
		if ( $rs_reviews_response->success ) {
			foreach ( $rs_reviews_response->reviews as $review ) {
				self::add_review( $profile_id, $review );
			}
			$summary_id = true;
		}

		return $summary_id;
	}

	/**
	 * Add Review
	 *
	 * @param int   $profile_id The ID of the profile for wich the review is from.
	 * @param array $review Review Data from Review Shake response.
	 *
	 * @return int|false The Review ID, otherwise false
	 */
	public static function add_review( $profile_id, $review ) {
		global $wpdb;
		$srm_review = array(
			'profile_id'     => $profile_id,
			'name'           => $review->name,
			'date'           => $review->date,
			'rating_value'   => $review->rating_value,
			'review_text'    => $review->review_text,
			'url'            => $review->url,
			'location'       => $review->location,
			'review_title'   => $review->review_title,
			'verified_order' => $review->verified_order,
			'language_code'  => $review->language_code,
			'reviewer_title' => $review->reviewer_title,
			'uuid'           => $review->unique_id,
			'meta_data'      => $review->meta_data,
		);
		// Check if review exists already, if so return its ID without inserting again
		$existing_review = self::get_review_by_uuid($review->unique_id);
		if ( ! empty( $existing_review ) ) {
			return $existing_review->id;
		}
		$result = $wpdb->insert( $wpdb->prefix . 'srm_reviews', $srm_review ); // db call ok; no-cache ok.
		if ( $result ) {
			// Save review avatar to uploads directory
			copy( $review->profile_picture, SRM_UPLOADS_DIR . 'srm_review_avatar_' . $review->unique_id . '.png' );
			return $wpdb->insert_id;
		} else {
			return false;
		}
	}

	/**
	 * Available sites supported by Review Shake for retrieving reviews from
	 *
	 * @return array List of supported sites
	 * @see    https://help.supervisor.reviewshake.com/article/34-supported-review-sites-scraper
	 */
	public static function get_review_sites() {
		return array(
			'AGODA'            => new SRM_REVIEW_SITES( 'Agoda', 'https://www.agoda.com/%s', SRM_PLUGIN_URL . '/assets/logos/agoda.jpg', '1' ),
			'AVVO'             => new SRM_REVIEW_SITES( 'Avvo', 'https://www.avvo.com/%s', SRM_PLUGIN_URL . '/assets/logos/avvo.png', '1' ),
			'EXPEDIA'          => new SRM_REVIEW_SITES( 'Expedia', 'https://www.expedia.com/%s', SRM_PLUGIN_URL . '/assets/logos/expedia.jpg', '1' ),
			'FACEBOOK'         => new SRM_REVIEW_SITES( 'Facebook', 'https://facebook.com/%s/reviews', SRM_PLUGIN_URL . '/assets/logos/facebook.png', '1' ),
			'GOOGLE'           => new SRM_REVIEW_SITES( 'Google', 'https://google.com/', SRM_PLUGIN_URL . '/assets/logos/google.png', '1' ),
			'GOOGLE_PLAYSTORE' => new SRM_REVIEW_SITES( 'Google Playstore', 'https://play.google.com/store/apps/details?id=%s&showAllReviews=true', SRM_PLUGIN_URL . '/assets/logos/google_playstore.png', '1' ),
			'TRIPADVISOR'      => new SRM_REVIEW_SITES( 'TripAdvisor', 'https://tripadvisor.com/%s', SRM_PLUGIN_URL . '/assets/logos/tripadvisor.png', '1' ),
			'TRUSTPILOT'       => new SRM_REVIEW_SITES( 'Trustpilot', 'https://trustpilot.com/review/%s', SRM_PLUGIN_URL . '/assets/logos/trustpilot.png', '1' ),
			'YELP'             => new SRM_REVIEW_SITES( 'Yelp', 'https://yelp.com/biz/%s',SRM_PLUGIN_URL . '/assets/logos/yelp.jpg', '1' ),
			'ZILLOW'           => new SRM_REVIEW_SITES( 'Zillow', 'https://zillow.com/profile/%s/#reviews',SRM_PLUGIN_URL . '/assets/logos/zillow.png', '1' ),
			'ZOMATO'           => new SRM_REVIEW_SITES( 'Zomato', 'https://zomato.com/%s/reviews',SRM_PLUGIN_URL . '/assets/logos/zomato.png', '1' ),
		);
	}

	/**
	 * Retrieve Profile Details. Requires a profile to be added first
	 *
	 * @param String $profile_id Profile ID
	 *
	 * @return Object $profile Profile Details
	 * @see    self::add_profile()
	 */
	public static function get_profile( $profile_id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}srm_view_reviews_profiles WHERE id = %d", $profile_id );

		$result = $wpdb->get_results( $sql, 'OBJECT_K' ); // db call ok; no-cache ok.

		return $result[ $profile_id ];
	}

	/**
	 * Retrieve Review by DataShake UUID
	 *
	 * @param String $uuid Review UUID from DataShake
	 *
	 * @return Object $review Review
	 */
	public static function get_review_by_uuid( $uuid ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}srm_reviews WHERE `uuid` = %s", $uuid );

		$result = $wpdb->get_results( $sql, 'OBJECT' ); // db call ok; no-cache ok.

		return $result[ 0 ];
	}

	/**
	 * Update the given profile to the provided status;
	 * if the provided status is invalid the resulting status will be ACTIVE (1)
	 *
	 * @param String $profile_id The ID of the Profile
	 * @param String $status     One of the supporting statuses EXPIRED, ACTIVE or DISABLED
	 *
	 * @return false|int
	 */
	public static function update_profile_status( $profile_id, $status ) {
		global $wpdb;
		switch ( $status ) {
			case self::EXPIRED:
				$value = 3;
				break;
			case self::ACTIVE:
				$value = 1;
				break;
			case self::DISABLED:
				$value = 0;
				break;
			default:
				$value = 1;
		}

		return $wpdb->update( $wpdb->prefix . 'srm_reviews_profiles', array( 'active' => (int) $value ), array( 'id' => $profile_id ) ); // no cache ok. db call ok.
	}

}