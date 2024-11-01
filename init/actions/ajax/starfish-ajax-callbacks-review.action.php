<?php

use Starfish\Premium\Reviews as SRM_REVIEWS;

/**
 * Callbacks for Review Ajax requests
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
 * @since      Release: 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Process visibility changes for a given review
 */
function srm_review_hide() {
	$is_hidden = rest_sanitize_boolean( $_POST['is_hidden'] );
	$review_id = sanitize_text_field( $_POST['review_id'] );
	if ( ! check_admin_referer(
		'srm_review',
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
		$srm_reviews = new SRM_REVIEWS();
		if ( $srm_reviews->update_review_hide( $review_id, $is_hidden ) !== 0 ) {
			echo wp_json_encode(
				array(
					'success' => true,
					'message' => 'Successfully Updated Review Hidden flag',
				)
			);
			wp_die();
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => 'Failed to update Review Hidden flag',
				)
			);
			wp_die();
		}
	}
}

add_action( 'wp_ajax_srm-review-hide', 'srm_review_hide' );
