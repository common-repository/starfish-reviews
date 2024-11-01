<?php

namespace Starfish;

use Freemius_Exception;
use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Logging as SRM_LOGGING;

/**
 * CRON JOBS
 *
 * Main class for creating Starfish Cron-Jobs
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1.0
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since      3.0
 * @see        https://developer.wordpress.org/plugins/cron/
 */
class Cron
{
    // Cron Hooks.
    public const CRON_EXPIRE_PROFILES = 'srm_cron_expire_profiles';
    public const CRON_SCRAPE_PROFILES = 'srm_cron_scrape_profiles';

    // Cron Schedules
    public const CRON_SCHEDULES = array(
        'DAILY'   => 'srm-every-day',
        'WEEKLY'  => 'srm-every-week',
        'MONTHLY' => 'srm-every-month',
    );

    public static function init()
    {

        add_filter('cron_schedules', array( __CLASS__, 'srm_add_cron_schedules' ));

    }

    /**
     * Adds custom cron schedules for Reviews management
     *
     * @param array $schedules An array of non-default cron schedules.
     *
     * @return array Filtered array of non-default cron schedules.
     */
    public static function srm_add_cron_schedules($schedules)
    {

        // Daily
        $schedules[ self::CRON_SCHEDULES['DAILY'] ] = array(
            'interval' => DAY_IN_SECONDS,
            'display'  => __('Every Day', 'starfish'),
        );
        // Weekly
        $schedules[ self::CRON_SCHEDULES['WEEKLY'] ] = array(
            'interval' => WEEK_IN_SECONDS,
            'display'  => __('Every Week', 'starfish'),
        );
        // Monthly
        $schedules[ self::CRON_SCHEDULES['MONTHLY'] ] = array(
            'interval' => MONTH_IN_SECONDS,
            'display'  => __('Every Month', 'starfish'),
        );
        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Setting SRM Cron Schedules',
                'message' => print_r($schedules, true),
                'code'    => 'SRM_C_CRON_X_1',
            )
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
    public static function srm_remove_cron_schedules($schedules)
    {
        unset($schedules[ self::CRON_SCHEDULES['DAILY'] ]);
        unset($schedules[ self::CRON_SCHEDULES['WEEKLY'] ]);
        unset($schedules[ self::CRON_SCHEDULES['MONTHLY'] ]);

        return $schedules;
    }

    /**
     * Schedule all Starfish system required Cron Jobs on activation ONLY
     *
     * @throws Freemius_Exception
     */
    public static function schedule_system_jobs()
    {
        // If NOT Free Plan
        if ('free' !== strtolower(SRM_FREEMIUS::starfish_fs()->get_plan_title())) {
            // Expire Profiles
            wp_clear_scheduled_hook(self::CRON_EXPIRE_PROFILES);
            wp_schedule_event(time(), self::CRON_SCHEDULES['DAILY'], self::CRON_EXPIRE_PROFILES);
        }
    }

    /**
     * Schedule all Cron Jobs with current settings on activation ONLY
     *
     * @throws Freemius_Exception
     */
    public static function schedule_all_cron_jobs()
    {
        // If NOT Free Plan.
        if ('free' !== strtolower(SRM_FREEMIUS::starfish_fs()->get_plan_title())) {
            // Scrape Profiles.
            wp_clear_scheduled_hook(self::CRON_SCRAPE_PROFILES);
            wp_schedule_event(time(), get_option(SRM_OPTION_REVIEW_SCRAPE_CRON), self::CRON_SCRAPE_PROFILES);
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
    public static function schedule_cron_job($cron, $schedule)
    {
        wp_clear_scheduled_hook($cron);
        return wp_schedule_event(time(), $schedule, $cron);
    }
}
