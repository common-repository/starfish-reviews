<?php

use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Feedback as SRM_FEEDBACK;
use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use Starfish\Twig as SRM_TWIG;
use Starfish\Migrations as SRM_MIGRATIONS;
use Twig\Error\LoaderError as LoaderErrorAlias;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Starfish\Premium\Collections as SRM_COLLECTIONS;
use Starfish\Funnels as SRM_FUNNELS;
use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;
use Starfish\Logging as SRM_LOGGING;

const NOTICE_TEMPLATE_INFO    = 'starfish-admin-notice-info.html.twig';
const NOTICE_TEMPLATE_WARNING = 'starfish-admin-notice-warning.html.twig';
const NOTICE_TEMPLATE_ERROR   = 'starfish-admin-notice-error.html.twig';

function srm_get_page_post_type() {
	if ( ! empty( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) ) ) {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
	} elseif ( ! empty( filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING ) ) ) {
		$page = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
	} else {
		$page = null;
	}
	return $page;
}
/**
 * Check if a notice is being dismissed, if so update the status to in-active
 *
 * @param $alert
 * @param null  $count
 * @param null  $limit
 * @param null  $violations
 * @return mixed
 */
function srm_show_notice( $alert, $count = null, $limit = null, $violations = null ) {
	if ( ! empty( $alert ) ) {
		$user_meta_data       = get_user_meta( get_current_user_id(), $alert, true );
		$current_notice_state = json_decode( $user_meta_data );
		if ( ! empty( $current_notice_state ) ) {
			$decision      = ( empty( $limit ) || empty( $current_notice_state->active ) || empty( $current_notice_state->count ) ) ? false : $current_notice_state->active;
			$current_value = $current_notice_state->count;
		} else {
			$decision      = false;
			$current_value = $count;
		}
		$new_value = $count;
		// Is the notice being dismissed?
		SRM_LOGGING::addEntry(
			array(
				'level'   => SRM_LOGGING::SRM_INFO,
				'action'  => 'Show Notice? (' . $alert . ')',
				'message' => ($decision) ? 'true' : 'false',
				'code'    => 'SRM_A_SN_N_01',
			)
		);
		if ( isset( $_GET['srm-admin-notice-dismiss'] ) && esc_html( $_GET['srm-admin-notice-dismiss'] ) == $alert ) {
			$decision = false;
		} else {
			// Is the notice regarding violations of license restrictions?
			if ( ! empty( $violations ) && $violations > 0 ) {
				$decision  = true;
				$new_value = $violations;
			}
			// Is the notice regarding new posts received recently?
			if ( ( ! empty( $count ) && empty( $limit ) ) && $count > $current_value ) {
				$decision  = true;
				$new_value = $count;
			}
			// Is the notice regarding the current license limit has been reached?
			if ( ! empty( $limit ) && $count === $limit ) {
				$decision  = true;
				$new_value = $count;
			} elseif ( ! empty( $limit ) && $count < $limit || 'unlimited' === $limit ) {
				$decision  = false;
				$new_value = $count;
			}
		}
		SRM_LOGGING::addEntry(
			array(
				'level'   => SRM_LOGGING::SRM_INFO,
				'action'  => 'Show Notice? (' . $alert . ')',
				'message' => ($decision) ? 'true' : 'false',
				'code'    => 'SRM_A_SN_N_02',
			)
		);
		return srm_update_notice_user_meta( $alert, $decision, $new_value );
	} else {
		return false;
	}
}

/**
 * Update or Add the notice dismissal value
 *
 * @param $alert string The notice being dismissed
 * @param $is_active bool The state of the notice
 * @param $value int The post count at time of updating
 *
 * @return object $meta object The new Meta Data value
 */
function srm_update_notice_user_meta( $alert, $is_active, $value ) {
	$data = wp_json_encode(
		array(
			'active' => ($is_active) ? 'true' : 'false',
			'date'   => strtotime( 'now' ),
			'count'  => $value,
		)
	);
	$meta = get_user_meta( get_current_user_id(), $alert, true );
	if ( !empty($meta) ) {
		update_user_meta( get_current_user_id(), $alert, $data );
	} else {
		add_user_meta( get_current_user_id(), $alert, $data );
	}
	$meta = get_user_meta( get_current_user_id(), $alert )[0];
	SRM_LOGGING::addEntry(
		array(
			'level'   => SRM_LOGGING::SRM_INFO,
			'action'  => 'Current Notice Meta (' . $alert . ')',
			'message' => wp_json_encode($meta),
			'code'    => 'SRM_A_UNUM_N_01',
		)
	);
	return json_decode( $meta );
}

/**
 * Present message for new users
 *
 * @return void
 * @throws LoaderErrorAlias
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_how_starfish_works() {
	$install_date = date_format( date_create( get_option( SRM_OPTION_INSTALL_DATE ) ), 'm/d/Y' );
	if ( strtotime( get_option( SRM_OPTION_INSTALL_DATE ) ) >= strtotime( '-7 day' ) ) {
		$page  = srm_get_page_post_type();
		$url   = esc_url( $_SERVER['REQUEST_URI'] );
		$alert = SRM_ADMIN_NOTICE_HOW_STARFISH_WORKS_ALERT;
		// Conduct dismissal process & status
		$notice_state = srm_show_notice( $alert );
		SRM_LOGGING::addEntry(
			array(
				'level'   => SRM_LOGGING::SRM_INFO,
				'action'  => 'Is Notice Active? (' . $alert . ')',
				'message' => print_r($notice_state->active, true) . '  = ' . filter_var($notice_state->active, FILTER_VALIDATE_BOOLEAN),
				'code'    => 'SRM_A_HSW_N_01',
			)
		);
		if ( isset( $notice_state->active ) && filter_var($notice_state->active, FILTER_VALIDATE_BOOLEAN) && false !== strpos( $url, $page ) ) {
			$message_c          = __( 'Looks like you might be new to Starfish Reviews, <strong>welcome!</strong>  Take a moment to review some useful information to help you get started. <p><a class="button button-primary" target="_blank" href="https://docs.starfish.reviews/category/185-how-starfish-works" title="How Starfish Works">How Starfish Reviews Works</a></p><small><span style="color:#ccc">Installation date within the last 7 days (' . $install_date . ')</span></small>', 'starfish' );
			$message            = sprintf( '<p>%s</p>', $message_c );
			$template_variables = array(
				'content'      => $message,
				'dismiss_meta' => $alert, // provide user meta data to be tracked
				'request_uri'  => $url, // return URL after dismissing
			);
			$twig               = SRM_TWIG::get_twig();
			echo $twig->render( NOTICE_TEMPLATE_INFO, $template_variables );
		}
	}
}

add_action( 'admin_notices', 'srm_how_starfish_works' );

/**
 * Report Plugin Migrations Pending.
 *
 * @return null
 * @throws LoaderErrorAlias
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_admin_notice_migrations_pending() {
	if ( get_transient( 'srm_pending_migrations' ) && ! empty( get_transient( 'srm_pending_migrations' ) ) ) {
		$message            = __( 'Starfish Reviews needs to perform updates to the database', 'starfish' );
		$sub_message        = __( 'Pending Updates:', 'starfish' );
		$instructions       = __( 'Click the button to allow Starfish to automatically perform these updates', 'starfish' );
		$pending_migrations = null;
		foreach ( SRM_MIGRATIONS::get_pending() as $migration ) {
			$pending_migrations .= '<p>[' . $migration->version . '] ' . $migration->name . ': ' . $migration->description . ' - ' . $migration->comment . '</p>';
		}
		$template_variables = array(
			'content' => sprintf(
				'<strong>%s</strong><br/>%s%s<button class="button-primary" id="srm-start-migrations">Update Now</button>&nbsp;&nbsp;<i>%s</i>',
				$message,
				$sub_message,
				$pending_migrations,
				$instructions
			),
		);

		$twig = SRM_TWIG::get_twig();
		echo $twig->render( NOTICE_TEMPLATE_WARNING, $template_variables );
	}
}

add_action( 'admin_notices', 'srm_admin_notice_migrations_pending' );

/**
 * Report any failed Schema Migration.
 */
function srm_admin_notice_migrations_failed() {
	if ( get_transient( 'srm_failed_migrations' ) && ! empty( get_transient( 'srm_failed_migrations' ) ) ) {
		$message           = __( 'The following Starfish Reviews migrations are currently in a FAILED state', 'starfish' );
		$instructions      = __( 'Click the button to refresh and try again', 'starfish' );
		$failed_migrations = null;
		foreach ( SRM_MIGRATIONS::get_failures() as $migration ) {
			$failed_migrations .= '<p>[' . $migration->version . '] ' . $migration->name . ': ' . $migration->description . ' - ' . $migration->comment . '</p>';
		}
		$template_variables = array(
			'content' => sprintf(
				'<strong>%s</strong>%s<button class="button-primary" id="srm-retry-migrations">Try Again</button>&nbsp;&nbsp;<i>%s</i>',
				$message,
				$failed_migrations,
				$instructions
			),
		);

		$twig = SRM_TWIG::get_twig();
		echo $twig->render( NOTICE_TEMPLATE_ERROR, $template_variables );
	}
}

add_action( 'admin_notices', 'srm_admin_notice_migrations_failed' );

/**
 * Report any successful Schema Migration.
 */
function srm_admin_notice_migrations_successful() {
	if ( get_transient( 'srm_successful_migrations' ) && ! empty( get_transient( 'srm_successful_migrations' ) ) ) {
		$migrations            = get_transient( 'srm_successful_migrations' );
		$message               = __( 'The following Starfish Reviews migrations completed successfully', 'starfish' );
		$successful_migrations = null;
		foreach ( $migrations as $migration ) {
			$successful_migrations .= '<p>[' . $migration->version . '] ' . $migration->name . ': ' . $migration->description . ' - ' . $migration->comment . '</p>';
		}
		$template_variables = array(
			'content' => sprintf(
				'<strong>%s</strong>%s',
				$message,
				$successful_migrations
			),
		);

		$twig = SRM_TWIG::get_twig();
		echo $twig->render( NOTICE_TEMPLATE_INFO, $template_variables );
	}
}

add_action( 'admin_notices', 'srm_admin_notice_migrations_successful' );

/**
 * Present a friendly message showing how many new feedback records have been received recently
 *
 * @throws Freemius_Exception
 * @throws LoaderErrorAlias
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_feedback_recent_records_message() {
	global $typenow;
	$alert    = SRM_ADMIN_NOTICE_FEEDBACK_NEW_ALERT;
	$page     = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
	$url      = esc_url( $_SERVER['REQUEST_URI'] );
	$days_ago = 1;
	$count    = SRM_FEEDBACK::srm_get_recent_feedback_count( $days_ago );

	// Conduct dismissal process & status
	$notice_state  = srm_show_notice( $alert, $count, null, null );
	$srm_error_cya = '01';
	if ( SRM_FREEMIUS::starfish_fs()->can_use_premium_code() && 'starfish_feedback' === $typenow && empty( $page ) && filter_var($notice_state->active, FILTER_VALIDATE_BOOLEAN) ) {
		SRM_LOGGING::addEntry(
			array(
				'level'   => SRM_LOGGING::SRM_INFO,
				'action'  => 'Admin Notice Trigger: ' . $alert,
				'message' => print_r( $notice_state, true ),
				'code'    => 'SRM_A_ANT_X_' . $srm_error_cya,
			)
		);
		$message            = sprintf( __( 'Hello, you received <strong>%1$s</strong> new Feedback in the last <strong>%2$s</strong> day(s)<br/>', 'starfish' ), $count, $days_ago );
		$template_variables = array(
			'content'      => $message,
			'dismiss_meta' => $alert, // provide user meta data to be tracked
			'request_uri'  => $url, // return URL after dismissing
		);
		$twig               = SRM_TWIG::get_twig();
		echo $twig->render( NOTICE_TEMPLATE_INFO, $template_variables );
	}
}

add_action( 'admin_notices', 'srm_feedback_recent_records_message' );

/**
 * Present warning that the number of Funnels, Collections, or Profiles are past the allowed number for the current plan.
 *
 * @throws LoaderErrorAlias
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_count_violation_message() {
	if ( ! empty( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	} elseif ( ! empty( filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
		$page = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	} else {
		$page = null;
	}
	$url         = esc_url( $_SERVER['REQUEST_URI'] );
	$plan_limits = SRM_FREEMIUS::srm_get_plan_limit();
	switch ( $page ) {
		case 'funnel':
			$limit      = $plan_limits['funnels'];
			$violations = SRM_FUNNELS::srm_force_extra_funnels_to_draft( $limit );
			$type       = 'funnel';
			$alert      = SRM_ADMIN_NOTICE_FUNNELS_COUNT_VIOLATIONS_ALERT;
			break;
		case 'collection':
			$limit      = $plan_limits['collections'];
			$violations = SRM_COLLECTIONS::srm_force_extra_collections_to_draft( $limit );
			$type       = 'collection';
			$alert      = SRM_ADMIN_NOTICE_COLLECTIONS_COUNT_VIOLATIONS_ALERT;
			break;
		case 'starfish-reviews-profiles':
			$limit      = $plan_limits['profiles'];
			$violations = SRM_UTILS_REVIEWS::srm_force_extra_profiles_to_disabled( $limit );
			$type       = 'profile';
			$alert      = SRM_ADMIN_NOTICE_PROFILES_COUNT_VIOLATIONS_ALERT;
			break;
		default:
			$limit      = null;
			$violations = null;
			$type       = null;
			$alert      = null;
	}
	// Conduct dismissal process & status
	$notice_state = srm_show_notice( $alert, null, null, $violations );
	if ( ( ! empty( $type ) && false !== strpos( $url, $type ) ) && filter_var($notice_state->active, FILTER_VALIDATE_BOOLEAN) ) {
		$current_plan_message     = _n( 'Your current plan (%1$s) allows for %2$s published %3$s at a time. %4$s', 'Your current plan (%1$s) allows for %2$s published %3$ss at a time. Want more? %4$s', $limit, 'starfish' );
		$message_c                = sprintf( $current_plan_message, SRM_PLAN_TITLE, '<strong>' . $limit . '</strong>', $type, SRM_GO_PREMIUM_LINK );
		$auto_restriction_message = __( 'The newest %1$ss have been un-published.', 'starfish' );
		$message_r                = sprintf( $auto_restriction_message, $type );
		$message                  = sprintf( '<p>%s<br/><br/><strong>%s</strong></p>', $message_c, $message_r );

		$template_variables = array(
			'content'      => $message,
			'dismiss_meta' => $alert, // provide user meta data to be tracked
			'request_uri'  => $url, // return URL after dismissing
		);

		$twig = SRM_TWIG::get_twig();
		echo $twig->render( NOTICE_TEMPLATE_WARNING, $template_variables );
	}
}

add_action( 'admin_notices', 'srm_count_violation_message' );

/**
 * Present info that the number of Funnels, Collections, or Profiles are at the current license limit.
 *
 * @return void
 * @throws LoaderErrorAlias
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_at_limit_message() {
	$page        = srm_get_page_post_type();
	$url         = esc_url( $_SERVER['REQUEST_URI'] );
	$plan_limits = SRM_FREEMIUS::srm_get_plan_limit();
	switch ( $page ) {
		case 'funnel':
			$limit = $plan_limits['funnels'];
			$count = SRM_FUNNELS::srm_get_total_funnels();
			$type  = 'funnel';
			$alert = SRM_ADMIN_NOTICE_FUNNELS_COUNT_ALERT;
			break;
		case 'collection':
			$limit = $plan_limits['collections'];
			$count = SRM_COLLECTIONS::srm_get_total_collections();
			$type  = 'collection';
			$alert = SRM_ADMIN_NOTICE_COLLECTIONS_COUNT_ALERT;
			break;
		case 'starfish-reviews-profiles':
			$limit = $plan_limits['profiles'];
			$count = count( SRM_UTILS_REVIEWS::get_profiles( true ) );
			$type  = 'profile';
			$alert = SRM_ADMIN_NOTICE_PROFILES_COUNT_ALERT;
			break;
		default:
			$limit = null;
			$count = null;
			$type  = null;
			$alert = null;
	}
	// Conduct dismissal process & status
	$notice_state = srm_show_notice( $alert, $count, $limit );
	if ( ! empty( $limit ) && ( filter_var($notice_state->active, FILTER_VALIDATE_BOOLEAN) && false !== strpos( $url, $page ) ) ) {
		$srm_error_cya = '01';
		SRM_LOGGING::addEntry(
			array(
				'level'   => SRM_LOGGING::SRM_INFO,
				'action'  => 'Admin Notice Trigger: Limit Reached (' . $alert . ')',
				'message' => print_r( $notice_state, true ),
				'code'    => 'SRM_F_ALM_A_' . $srm_error_cya,
			)
		);
		$current_plan_message = _n( 'Your current plan (%1$s) allows for %2$s published %3$s at a time. %4$s', 'Your current plan (%1$s) allows for %2$s published %3$ss at a time. Want more? %4$s', $limit, 'starfish' );
		$message_c            = sprintf( $current_plan_message, SRM_PLAN_TITLE, '<strong>' . $limit . '</strong>', $type, SRM_GO_PREMIUM_LINK );
		$message              = sprintf( '<p>%s</p>', $message_c );
		$template_variables   = array( 'content' => $message );
		$twig                 = SRM_TWIG::get_twig();
		echo $twig->render( NOTICE_TEMPLATE_INFO, $template_variables );
	}
}

add_action( 'admin_notices', 'srm_at_limit_message' );

/**
 * Present warning that the WordPress APIs are potentially disabled, or otherwise inaccessible.
 * Starfish Review Scraping/importing require the APIs to be accessible.
 *
 * @return void
 * @throws LoaderErrorAlias
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_api_disabled_message() {

	// Check if WordPress APIs are accessible.
	$srm_error_cya = '01';
	// Check transient
	if ( ! get_transient( 'srm_apis_accessible' ) ) {
		$cookies = wp_unslash( $_COOKIE );
		$timeout = 8;
		$headers = array(
			'Cache-Control' => 'no-cache',
			'X-WP-Nonce'    => wp_create_nonce( 'wp_rest' ),
		);
		$url     = SRM_SITE_URL . '?rest_route=/starfish/canary';
		$result  = wp_remote_get( $url, compact( 'cookies', 'headers', 'timeout' ) );
		if ( 200 !== wp_remote_retrieve_response_code( $result ) || is_wp_error( $result ) ) {
			set_transient( 'srm_apis_accessible', false, '604800' );
			SRM_LOGGING::addEntry(
				array(
					'level'   => SRM_LOGGING::SRM_WARN,
					'action'  => 'Admin Notice Trigger: API Accessibility Warning',
					'message' => print_r( $result, true ),
					'code'    => 'SRM_A_ANT_A_' . $srm_error_cya,
				)
			);
			$message            = __( '<p>WordPress APIs are not accessible. Starfish Reviews requires the use of the <a href="https://developer.wordpress.org/rest-api/reference/" target="_blank">WordPress API Framework</a> in order for the Review Importing to function properly. <p><button class="button" onclick="Beacon(\'article\', \'62c70b68eabe9a7235b3b7b1\', { type: \'modal\' })">Learn More</button></p></p>', 'starfish' );
			$template_variables = array( 'content' => $message );
			$twig               = SRM_TWIG::get_twig();
			echo $twig->render( NOTICE_TEMPLATE_WARNING, $template_variables );
		} else {
			set_transient( 'srm_apis_accessible', true, '604800' );
			$srm_error_cya = '02';
			SRM_LOGGING::addEntry(
				array(
					'level'   => SRM_LOGGING::SRM_INFO,
					'action'  => 'Starfish APIs are accessible!',
					'message' => wp_remote_retrieve_body( $result ),
					'code'    => 'SRM_A_ANT_A_' . $srm_error_cya,
				)
			);
		}
	}
}

if ( SRM_FREEMIUS::starfish_fs()->can_use_premium_code() && SRM_FREEMIUS::starfish_fs()->is__premium_only() ) {
	add_action( 'admin_notices', 'srm_api_disabled_message' );
}

/**
 * Check if Rich Reviews is installed and ask to migrate Reviews to Testimonials.
 */
function srm_admin_notice_migrations_richreviews() {
	$is_migrated = get_option( SRM_OPTION_RICHREVIEWS_MIGRATED );
	if ( is_plugin_active( 'rich-reviews/rich-reviews.php' ) && !$is_migrated ) {
		$message = __( 'Looks like you have Rich Reviews installed. Would you like to migrate your Reviews to Starfish Reviews Testimonials? (Your Rich Reviews records will not be removed). To learn more about the new Testimonial feature read our <a href="https://docs.starfish.reviews/category/201-testimonials" target="_blank">Help Docs</a>.', 'starfish' );
		$action  = '<p><button class="button-primary" id="srm-start-richreviews-migrations">Migrate Now</button></p>';
		$template_variables = array(
			'content' => sprintf(
				'%s<br/>%s',
				$message,
				$action
			),
		);

		$twig = SRM_TWIG::get_twig();
		echo $twig->render( NOTICE_TEMPLATE_INFO, $template_variables );
	}
}

add_action( 'admin_notices', 'srm_admin_notice_migrations_richreviews' );
