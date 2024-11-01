<?php

use Starfish\Migrations as SRM_MIGRATIONS;
use Starfish\Settings as SRM_SETTINGS;
/**
 * Install default data and perform activation related steps
 */
function srm_plugin_install() {

	$srm_install_date = get_option( SRM_OPTION_INSTALL_DATE );

	// Ingest any new migration schemas.
	SRM_MIGRATIONS::ingest();

	// check if options exist, if not create defaults.
	// ================================================.
	foreach ( SRM_SETTINGS::srm_get_options( 'general' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}
	foreach ( SRM_SETTINGS::srm_get_options( 'funnel_general' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}
	foreach ( SRM_SETTINGS::srm_get_options( 'funnel_email' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}
	foreach ( SRM_SETTINGS::srm_get_options( 'reviews' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}
	foreach ( SRM_SETTINGS::srm_get_options( 'support' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}
	foreach ( SRM_SETTINGS::srm_get_options( 'testimonial_general' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}
	foreach ( SRM_SETTINGS::srm_get_options( 'testimonial_form' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}
	foreach ( SRM_SETTINGS::srm_get_options( 'testimonial_email' ) as $option => $args ) {
		add_option( $option, $args['default'] );
	}

	// Set transients & perform migrations.
	// ===================================.
	set_transient( 'srm_activation_complete', true, 60 );

	// Set new install transient if brand-new installation; perform all migrations automatically.
	if ( ! $srm_install_date ) {
		add_option( SRM_OPTION_INSTALL_DATE, date( 'Y-m-d H:i:s' ) );
		set_transient( 'srm_new_install', true, 10 );
		SRM_MIGRATIONS::execute_pending();
	}

	// Clear User Meta Data.
	// ===================================.
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_FEEDBACK_NEW_ALERT );
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_FUNNELS_COUNT_ALERT );
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_COLLECTIONS_COUNT_ALERT );
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_PROFILES_COUNT_ALERT );
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_FUNNELS_COUNT_VIOLATIONS_ALERT );
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_COLLECTIONS_COUNT_VIOLATIONS_ALERT );
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_PROFILES_COUNT_VIOLATIONS_ALERT );
	delete_user_meta( get_current_user_id(), SRM_ADMIN_NOTICE_HOW_STARFISH_WORKS_ALERT );

}