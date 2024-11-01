<?php

use Starfish\Testimonials as SRM_TESTIMONIALS;
use Starfish\Settings as SRM_SETTINGS;

/**
 * Callbacks for Testimonial Ajax requests
 *
 * @category   Testimonials
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0
 * @since      Release: 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Process visibility changes for a given review
 */
function srm_testimonial_hide() {
	$is_hidden      = rest_sanitize_boolean( $_POST['is_hidden'] );
	$testimonial_id = sanitize_text_field( $_POST['testimonial_id'] );
	if ( ! check_admin_referer(
		'srm_testimonial',
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
		$srm_testimonials = new SRM_TESTIMONIALS();
		if ( $srm_testimonials->srm_update_testimonial_hide( $testimonial_id, $is_hidden ) != 0 ) {
			echo wp_json_encode(
				array(
					'success' => true,
					'message' => 'Successfully Updated Testimonial Hidden flag',
				)
			);
			wp_die();
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => 'Failed to update Testimonial Hidden flag',
				)
			);
			wp_die();
		}
	}
}

add_action( 'wp_ajax_srm-testimonial-hide', 'srm_testimonial_hide' );

/**
 * Process new testimonial submission
 */
function srm_testimonial_submit() {
	if ( ! check_ajax_referer( 'srm-testimonial-submit', 'security' ) ) {
		echo wp_json_encode(
			array(
				'success' => false,
				'message' => 'You do not have rights to perform this action',
			)
		);
	} elseif ( isset( $_POST['data'] ) ) {
		parse_str($_POST['data'], $submission);
		$success_message = esc_html( get_option(SRM_OPTION_TESTIMONIAL_FORM_SUCCESS, SRM_SETTINGS::srm_get_options( 'testimonial_form' )[ SRM_OPTION_TESTIMONIAL_FORM_SUCCESS ]['default'] ) );
		if ( empty( $submission['srm-testimonial-hp'] ) ) {
			try {
				SRM_TESTIMONIALS::srm_insert_testimonial($submission);
				echo wp_json_encode(['success' => true, 'message' => $success_message]);
			} catch(Exception $e) {
				echo wp_json_encode(['success' => false, 'message' => 'Failed to insert new testimonial (' . $e . ')']);
			}
		} else {
			echo wp_json_encode(['success' => false, 'message' => 'Failed to verify human (HP=' . $submission['srm-testimonial-hp'] .')']);
		}
	} else {
		echo wp_json_encode(['success' => false, 'message' => 'Failed to capture submission',]);
	}
	wp_die();
}

add_action( 'wp_ajax_srm-testimonial-submit', 'srm_testimonial_submit' );
add_action( 'wp_ajax_nopriv_srm-testimonial-submit', 'srm_testimonial_submit' );
