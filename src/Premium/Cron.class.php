<?php

namespace Starfish\Premium;

use Freemius_Exception;
use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;

/**
 * CRON JOBS
 *
 * Main class for creating Starfish Cron-Jobs
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1.0.0
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since      3.0.0
 * @see        https://developer.wordpress.org/plugins/cron/
 */
class Cron {
	// Cron Hooks
	const CRON_EXPIRE_PROFILES = 'srm_cron_expire_profiles';
	const CRON_SCRAPE_PROFILES = 'srm_cron_scrape_profiles';

	// Cron Schedules
	const CRON_SCHEDULES = array(
		'DAILY'   => 'srm-every-day',
		'WEEKLY'  => 'srm-every-week',
		'MONTHLY' => 'srm-every-month',
	);

	public function __construct() {
        // Add custom cron schedules
        add_filter( 'cron_schedules', array( __class__, 'srm_add_cron_schedules' ) );
	}

	/**
	 * Adds custom cron schedules for Reviews management
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array Filtered array of non-default cron schedules.
	 */
	public static function srm_add_cron_schedules( $schedules ) {
		$interval = 'interval';
		$display  = 'display';

		// Daily
		$schedules[ self::CRON_SCHEDULES['DAILY'] ] = array(
			$interval => DAY_IN_SECONDS,
			$display  => __( 'Every Day', 'starfish' ),
		);
		// Weekly
		$schedules[ self::CRON_SCHEDULES['WEEKLY'] ] = array(
			$interval => WEEK_IN_SECONDS,
			$display  => __( 'Every Week', 'starfish' ),
		);
		// Monthly
		$schedules[ self::CRON_SCHEDULES['MONTHLY'] ] = array(
			$interval => MONTH_IN_SECONDS,
			$display  => __( 'Every Month', 'starfish' ),
		);

		return $schedules;
	}

	/**
	 * Remove custom cron schedules for Reviews management
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array Filtered array of default cron schedules.
	 */
	public static function srm_remove_cron_schedules( $schedules ) {
		unset( $schedules[ self::CRON_SCHEDULES['DAILY'] ] );
		unset( $schedules[ self::CRON_SCHEDULES['WEEKLY'] ] );
		unset( $schedules[ self::CRON_SCHEDULES['MONTHLY'] ] );

		return $schedules;
	}

	/**
	 * Cron job for expiring any profiles 30-days or older
	 */
	public static function srm_cron_expire_profiles_cb() {
		foreach ( SRM_REVIEWS_PROFILES::get_reviews_profiles() as $profile ) {
			if ( SRM_REVIEWS_PROFILES::is_expired( $profile['id'], $profile['updated'] ) ) {
                SRM_UTILS_REVIEWS::update_profile_status( $profile['id'], SRM_UTILS_REVIEWS::EXPIRED );
			}
		}
	}

	/**
	 * Cron job for scraping profiles for reviews
	 */
	public static function srm_cron_scrape_profiles_cb() {
		foreach ( SRM_REVIEWS_PROFILES::get_reviews_profiles() as $profile ) {
			// Check if profile is active (not disabled or expired)
			if ( $profile['active'] ) {
				// Make sure profile's update date is not 30 days or older (expired)
				if ( SRM_REVIEWS_PROFILES::is_expired( $profile['id'], $profile['updated'] ) ) {
                    SRM_REVIEWS_PROFILES::scrape_reviews( $profile['id'], $profile['job_id'] );
				} else {
                    SRM_UTILS_REVIEWS::update_profile_status( $profile['id'], SRM_UTILS_REVIEWS::EXPIRED );
				}
			}
		}
	}

	/**
	 * Schedule all Starfish\Premium system required Cron Jobs on activation ONLY
	 *
	 * @throws Freemius_Exception
	 */
	public static function schedule_system_jobs() {
		// If NOT Free Plan
		if ( 'free' !== strtolower( SRM_FREEMIUS::starfish_fs()->get_plan_title() ) ) {
			wp_clear_scheduled_hook( self::CRON_EXPIRE_PROFILES );
			wp_schedule_event( time(), self::CRON_SCHEDULES['DAILY'], self::CRON_EXPIRE_PROFILES );
		}
	}

	/**
	 * Schedule all Cron Jobs with current settings on activation ONLY
	 *
	 * @throws Freemius_Exception
	 */
	public static function schedule_all_cron_jobs() {
		// If NOT Free Plan
		if ( 'free' !== strtolower( SRM_FREEMIUS::starfish_fs()->get_plan_title() ) ) {
			wp_clear_scheduled_hook( self::CRON_SCRAPE_PROFILES );
			wp_schedule_event( time(), get_option( SRM_OPTION_REVIEW_SCRAPE_CRON ), self::CRON_SCRAPE_PROFILES );
		}
	}

	/**
	 * Schedule Cron Jobs defined in the Plugin Settings options
	 *
	 * @param string $cron The cron job (hook) to update
	 * @param string $schedule The new schedule to update the cron to
	 *
	 * @return boolean True if successful; false if on free plan or no profiles exist
     */
	public static function schedule_cron_job( $cron, $schedule ) {
		wp_clear_scheduled_hook( $cron );
		return wp_schedule_event( time(), $schedule, $cron );
	}

}