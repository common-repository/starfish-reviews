<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Process Plugin Migrations; triggered from Admin Notice.
 *
 * @see /init/actions/starfish-admin-notices.action.php
 * @see /js/starfish-admin-general.js
 **/
function srm_start_plugin_migrations() {

	try {
		SRM_MIGRATIONS::execute_pending();
		echo wp_json_encode( array( "msg" => "success" ) );
	}
	catch( Exception $e ) {
		echo wp_json_encode( array( "msg" => "fail" ) );
	}

	wp_die();

}
add_action( 'wp_ajax_starfish-execute-migrations', 'srm_start_plugin_migrations' );

/**
 * Process Restoration of Options; triggered from Admin Funnel or Settings.
 * If $_POST['funnel_id'] is provided then the funnel will be restored to current default values.
 * Otherwise, the current defaults will be restored to original system default values
 *
 * @see /init/actions/starfish-admin-notices.action.php
 * @see /js/starfish-admin-funnel.js
 **/
function srm_restore_options_defaults() {

	$options = $_POST['options'];
	if( isset($_POST['funnel_id']) ) {
		foreach( $options as $option => $args ) {
			update_post_meta( $_POST['funnel_id'], $args['funnel_cf_id'], $args['current'] );
		}
	}
	else {
		foreach( $options as $option => $args ) {
			update_option( $option, stripslashes($args['default']) );
		}
	}
	echo wp_json_encode( array("type" => "success") );
	wp_die();

}
add_action( 'wp_ajax_starfish-execute-restore-default-options', 'srm_restore_options_defaults' );

/**
 * Export Feedback
 *
 **/
function srm_export_feedback() {

    // Verify if the nonce is valid
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'srm-export-feedback')) {
        echo wp_json_encode( array("type" => "failed") );
        wp_die();
    }
    $feedback  = null;
    $funnel_id = null;
    $month     = null;
    if ( isset( $_POST[ 'month' ] ) ) {
        $month = $_POST[ 'month' ];
    }

    if ( isset( $_POST[ 'funnel_id' ] ) ) {
        $funnel_id = $_POST[ 'funnel_id' ];
    }

    // retrieve feedback
    $feedback = SRM_FEEDBACK::srm_get_feedback( $funnel_id, $month );

    // export feedback to CSV
    $filename = "starfish_feedback_" . strtotime( "now" ) . ".csv";

    // create a file pointer connected to the output stream
    $output = fopen( SRM_PLUGIN_PATH . '/exports/' . $filename, 'w' );

    // output the column headings
    fputcsv( $output, array( 'Date', 'ID', 'Reviewer Name', 'Reviewer Phone', 'Reviewer Email', 'Feedback', 'Message', 'Funnel', 'Destination' ) );

    // loop over the rows, outputting them
    while ( $feedback->have_posts() ) {
        $feedback->the_post();
        $response = get_post_meta( get_the_ID(), SRM_META_REVIEWER_FEEDBACK, true );
        $record   = array(
            "date"           => get_the_date(),
            "id"             => get_post_meta( get_the_ID(), SRM_META_TRACKING_ID, true ),
            "reviewer_name"  => get_post_meta( get_the_ID(), SRM_META_REVIEWER_NAME, true ),
            "reviewer_phone" => get_post_meta( get_the_ID(), SRM_META_REVIEWER_PHONE, true ),
            "reviewer_email" => get_post_meta( get_the_ID(), SRM_META_REVIEWER_EMAIL, true ),
            "feedback"       => ( true === filter_var( $response, FILTER_VALIDATE_BOOLEAN ) ) ? 'Positive' : 'Negative',
            "message"        => get_the_content(),
            "funnel"         => get_the_title( get_post_meta( get_the_ID(), SRM_META_FUNNEL_ID, true ) ),
            "destination"    => get_post_meta( get_the_ID(), SRM_META_DESTI_NAME, true ),
        );
        fputcsv( $output, $record );
    }
    echo wp_json_encode( array("type" => "success", "url" => SRM_PLUGIN_URL . '/exports/' . $filename, "file_name" => basename($filename, ".csv") ) );
    wp_die();

}
add_action( 'wp_ajax_srm-export-feedback', 'srm_export_feedback' );

/**
 * Delete Feedback Export
 *
 **/
function srm_delete_feedback_export() {
    // Verify if the nonce is valid
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'srm-export-feedback')) {
        echo wp_json_encode( array("type" => "failed") );
        wp_die();
    }
    if( isset( $_POST[ 'file' ] ) && unlink(SRM_PLUGIN_PATH . '/exports/' . esc_html($_POST[ 'file' ]) . '.csv') ){
        echo wp_json_encode( array("type" => "success") );
        wp_die();
    }
    else {
        echo wp_json_encode( array("type" => "failed") );
        wp_die();
    }
}
add_action( 'wp_ajax_srm-delete-feedback-export', 'srm_delete_feedback_export' );