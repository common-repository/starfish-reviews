<?php

use Starfish\Cron as SRM_CRON;

/**
 * Reschedule Settings Cron Jobs
 */
add_action(
	'update_option',
	function ( $option_name, $old_value, $value ) {
		if ( $option_name === SRM_OPTION_REVIEW_SCRAPE_CRON ) {
			// Reset Settings Related Cron Schedules.
			SRM_CRON::schedule_cron_job( SRM_CRON::CRON_SCRAPE_PROFILES, $value );
		}
	},
	10,
	3);
