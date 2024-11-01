<?php

add_action('admin_post_srm_export_feedback', 'srm_handle_export_feedback');
function srm_handle_export_feedback() {

    // Verify if the nonce is valid
    if ( !isset($_POST['export_feedback_nonce']) || !wp_verify_nonce($_POST['export_feedback_nonce'], 'srm-export-feedback')) {
        return;
    }
    $feedback  = null;
    $funnel_id = null;
    $month     = null;
    if ( isset( $_POST[ 'srm-feedback-month' ] ) ) {
        $month = $_POST[ 'srm-feedback-month' ];
    }

    if ( isset( $_POST[ 'srm-feedback-funnelid' ] ) ) {
        $funnel_id = $_POST[ 'srm-feedback-funnelid' ];
    }

    // retrieve feedback
    $feedback = SRM_FEEDBACK::srm_get_feedback( $funnel_id, $month );

    // export feedback to CSV
    // output headers so that the file is downloaded rather than displayed
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=starfish_feedback_' . strtotime( "now" ) . '.csv' );

    // create a file pointer connected to the output stream
    $output = fopen( 'php://output', 'w' );

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

}