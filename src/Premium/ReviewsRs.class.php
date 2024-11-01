<?php

namespace Starfish\Premium;

use Starfish\Logging as SRM_LOGGING;
use function print_r;

/**
 * Methods for interacting with external review imports
 *
 * Integration with ReviewShake's API for adding profiles, retrieving reviews from external sources (i.e. Google,
 * Facebook etc)
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0.0
 * @link       https://api.supervisor.reviewshake.com
 * @LINK       https://supervisor.reviewshake.com/
 * @since      Release: 2.4.0
 */
class ReviewsRs {

	/**
	 * ReviewShake constants
	 */
	const RS_HOST        = 'https://app.datashake.com/api/';
	const RS_VERSION     = 'v2';
	const RS_ADD_PROFILE = self::RS_HOST . self::RS_VERSION . '/profiles/add';
	const RS_GET_REVIEWS = self::RS_HOST . self::RS_VERSION . '/profiles/reviews';
	const RS_GET_PROFILE = self::RS_HOST . self::RS_VERSION . '/profiles/info';
	const RS_CALLBACK    = SRM_SITE_URL . '?rest_route=/starfish/reviews/summary';

	/**
	 * ReviewShake token for authenticating requests
	 * @var |null
	 */
	private static $rs_token = '1d35a1cbeb84ec7aa1c2e53858a6ff622906c90c';

    /**
	 * SRM_REVIEWS constructor.
	 */
	function __construct() { }

	private static function get_request_headers() {
        return array(
            'headers' => array(
                'spiderman-token' => self::$rs_token,
            ),
        );
    }
	/**
	 * Add a new reviews profile to the Review Shake account
	 *
	 * @param array $profile_data the profile details.
	 *
	 * @return mixed|false $profile The JSON Encoded Object Review Shake profile details upon successful submission, otherwise FALSE
	 */
	public static function add_profile( $profile_data ) {
        $srm_error_cya = '01';
		if ( ! empty( $profile_data ) ) {
			if ( stripos( $profile_data['url'], 'google' ) !== false ) {
				$url = self::RS_ADD_PROFILE;
				if(isset($profile_data['place_id']) && !empty($profile_data['place_id'])) {
					$url .= '?place_id=' . $profile_data['place_id'];
				} elseif(isset($profile_data['query']) && !empty($profile_data['query'])) {
					$url .= '?query=' . $profile_data['query'];
				}
				$url .= '&diff=' . $profile_data['job_id'];
				$url .= '&callback=' . self::RS_CALLBACK;
			} else {
				$url = self::RS_ADD_PROFILE .
					   '?url=' . rawurlencode( $profile_data['url'] ) .
					   '&diff=' . $profile_data['job_id'] .
					   '&callback=' . self::RS_CALLBACK;
			}
            // Log the initial request being made to Datashake
            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'DS Request',
                    'message' => 'request=' . print_r($url, true),
                    'code'    => '3RD_R_DSAP_X_' . $srm_error_cya
                ));
            $srm_error_cya = '02';
			$response = wp_remote_post(
				$url,
				self::get_request_headers()
			);
            // Log the response received from the Datashake request
            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'DS Response',
                    'message' => 'response=' . print_r($response, true),
                    'code'    => '3RD_R_DSAP_X_' . $srm_error_cya
                ));
			if ( ! is_wp_error( $response ) ) {
				return json_decode( $response['body'] );
			} else {
				return $response->get_error_message();
			}
		} else {
			return false;
		}
	}

	/**
	 * Get the reviews for a given job ID, requires the profile to have been added already
	 *
	 * @param string $job_id the profile job_id.
	 *
	 * @return mixed|false The reviews of the given profile's job ID, with summary
	 * @see add_profile()
	 */
	public static function get_reviews( $job_id ) {
		if ( ! empty( $job_id ) ) {
			$response = wp_remote_get(
				self::RS_GET_REVIEWS .
				'?job_id=' . $job_id .
				'&per_page=100',
				self::get_request_headers()
			);
			if ( ! is_wp_error( $response ) ) {
				// Aggregate all reviews if more than one result set available
				return json_decode( $response['body'] );
			} else {
				return $response->get_error_message();
			}
		} else {
			return false;
		}
	}

}