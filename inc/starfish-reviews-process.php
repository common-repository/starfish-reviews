<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
**Process Submitted Reviews
**/
function srm_send_starfish_review_data() {
    
	$funnel_id   = intval($_POST['funnel_id']);
	$tracking_id = sanitize_text_field($_POST['tracking_id']);
	$yes_no_flag = sanitize_text_field($_POST['yes_no_flag']);
	$desti_type  = sanitize_text_field( $_POST['funnel_desti_type'] );

	$current_local_time = date( 'j M Y g:i a', current_time( 'timestamp', 0 ) );

	if ( ! check_ajax_referer( 'srm_review_nonce', 'security' ) ) {
		echo json_encode(array("msg" => "Error"));
		exit;
	}else{
		$defaults_review_arg = array(
			'post_type'   => 'starfish_review',
			'post_title'  => $current_local_time,
			'post_status' => 'publish'
		);
		if ( $review_id = wp_insert_post( $defaults_review_arg ) ) {
			add_post_meta( $review_id, '_srm_feedback', $yes_no_flag );
			add_post_meta( $review_id, '_srm_funnel_id', $funnel_id );
			add_post_meta( $review_id, '_srm_tracking_id', $tracking_id );
			add_post_meta( $review_id, '_srm_desti_type', $desti_type );
			add_post_meta( $review_id, '_srm_desti_name', 'No' );
		}
		echo json_encode(array("msg" => "Complete", "review_id" => $review_id));
		exit;
	}

}
add_action('wp_ajax_send-starfish-review-data', 'srm_send_starfish_review_data');
add_action('wp_ajax_nopriv_send-starfish-review-data', 'srm_send_starfish_review_data');


/**
 * Update Review Post
 */
function srm_update_starfish_review_data() {

	$review_id      = intval( $_POST['review_id'] );
	$funnel_id      = intval( $_POST['funnel_id'] );
	$tracking_id    = sanitize_text_field( $_POST['tracking_id'] );
	$yes_no_flag    = sanitize_text_field( $_POST['yes_no_flag'] );
	$desti_type     = sanitize_text_field( $_POST['desti_type'] );
	$desti_name     = sanitize_text_field( $_POST['desti_name'] );
	$desti_url      = sanitize_text_field( $_POST['desti_url'] );
	$reviewer_name  = sanitize_text_field( $_POST['reviewer_name'] );
	$reviewer_email = sanitize_text_field( $_POST['reviewer_email'] );
	$reviewer_phone = sanitize_text_field( $_POST['reviewer_phone'] );
	$review_text    = sanitize_text_field( $_POST['review_text'] );

	if ( ! check_ajax_referer( 'srm_review_nonce', 'security' ) ) {
		echo json_encode( array( "msg" => "Error" ) );
		exit;
	} else {
		if ( $desti_type === 'single' ) {
			if ( ! empty( $desti_url ) ) {
				$url = parse_url( $desti_url );
				update_post_meta( $review_id, '_srm_desti_name', $url['host'] );
			}
		}
		if ( $desti_type === 'multiple' ) {
			if ( ! empty( $desti_name ) ) {
				update_post_meta( $review_id, '_srm_desti_name', $desti_name );
			}
			if ( ! empty( $desti_url ) ) {
				update_post_meta( $review_id, '_srm_destination_url', $desti_url );
			}
		}
		if ( ! empty( $reviewer_name ) ) {
			update_post_meta( $review_id, '_srm_reviewer_name', $reviewer_name );
		}
		if ( ! empty( $reviewer_email ) ) {
			update_post_meta( $review_id, '_srm_reviewer_email', $reviewer_email );
		}
		if ( ! empty( $reviewer_phone ) ) {
			update_post_meta( $review_id, '_srm_reviewer_phone', $reviewer_phone );
		}
		if ( ! empty( $review_text ) ) {
			$args = array(
				'ID'           => $review_id,
				'post_content' => $review_text,
			);
			wp_update_post( $args );
		}
		//Send email if negative feedback
		if ( $yes_no_flag . strtolower() != 'yes' ) {
			$review_message = sanitize_text_field( $review_text );
			srm_send_feedback_email( $funnel_id, $review_message, $tracking_id, $review_id );
		}
		echo json_encode( array( "msg" => "Complete" ) );
		exit;
	}

}
add_action('wp_ajax_update-starfish-review-data', 'srm_update_starfish_review_data');
add_action('wp_ajax_nopriv_update-starfish-review-data', 'srm_update_starfish_review_data');

function srm_send_feedback_email( $funnel_id, $review_message, $tracking_id, $review_id ) {

	$admin_email = get_option('admin_email');
	$blogname = get_option('blogname');

	$funnel_name = get_the_title($funnel_id);
	$email_subject = stripslashes(get_option('srm_email_subject'));
	$search_subject = array();
	$replace_subject = array();
	$search_subject[] = '{review-id}';
	$replace_subject[] = $tracking_id;
	$search_subject[] = '{funnel-name}';
	$replace_subject[] = $funnel_name;
	$email_subject = str_replace($search_subject, $replace_subject, $email_subject);
	$email_subject = wp_specialchars_decode( esc_html( $email_subject ), ENT_QUOTES );

	$reviewer_name = esc_html(get_post_meta($review_id, '_srm_reviewer_name', true));
	$reviewer_email = esc_html(get_post_meta($review_id, '_srm_reviewer_email', true));
	$reviewer_phone = esc_html(get_post_meta($review_id, '_srm_reviewer_phone', true));

	//process email message
	$email_message = get_option('srm_email_template');
	$search        = array();
	$replace       = array();
	$search[]      = '{admin-email}';
	$replace[]     = $admin_email;
	$search[]      = '{site-name}';
	$replace[]     = $blogname;
	$search[]      = '{funnel-name}';
	$replace[]     = $funnel_name;
	$search[]      = '{review-id}';
	$replace[]     = $tracking_id;
	$search[]      = '{reviewer-name}';
	$replace[]     = $reviewer_name;
	$search[]      = '{reviewer-email}';
	$replace[]     = $reviewer_email;
	$search[]      = '{reviewer-phone}';
	$replace[]     = $reviewer_phone;
	$search[]      = '{review-message}';
	$replace[]     = $review_message;
	$get_message   = str_replace($search, $replace, $email_message);
	$get_message   = 	stripslashes($get_message);
	$message_send  = apply_filters('the_content', $get_message);

	//process email addresses
	$srm_email_feedback  = esc_html(get_post_meta( $funnel_id, '_srm_email_feedback', true ));
	$search              = array();
	$replace             = array();
	$search[]            = '{admin-email}';
	$replace[]           = $admin_email;
	$get_email_addresses = str_replace($search, $replace, $srm_email_feedback);
	$email_addresses     = explode( ",", $get_email_addresses );

	$srm_email_from_name = esc_html(stripslashes(get_option('srm_email_from_name')));
	$search = array();
	$replace = array();
	$search[] = '{site-name}';
	$replace[] = $blogname;
	$srm_email_from_name = str_replace($search, $replace, $srm_email_from_name);

	$srm_email_from_email = esc_html(get_option('srm_email_from_email'));
	$search = array();
	$replace = array();
	$search[] = '{admin-email}';
	$replace[] = $admin_email;
	$srm_email_from_email = str_replace($search, $replace, $srm_email_from_email);

	//if tracking id is valid email address then use it for replay to email.
	$srm_email_replay_to_email = '';
	if(is_email( $tracking_id ) && (get_option('srm_replay_to_email') == 'yes')){
		$srm_email_replay_to_email = $tracking_id;
	}

	if(is_email( $reviewer_email )){
		$srm_email_replay_to_email = $reviewer_email;
	}

	$headers[]  = 'MIME-Version: 1.0' . "\r\n";
	$headers[] = 'Content-Type: text/html; charset=UTF-8' . "\r\n";

	if(($srm_email_from_name != '') && ($srm_email_from_email != '')){
		$headers[] = "From: " . wp_specialchars_decode( esc_html( $srm_email_from_name ), ENT_QUOTES ) . " <" . apply_filters( 'starfish_notification_email_header_from_email', $srm_email_from_email ) . ">\r\n";
	}
	if(($srm_email_replay_to_email != '')){
		$headers[] = 'Reply-To: <' . $srm_email_replay_to_email . '>';
	}

	if ( ! empty( array_filter( $email_addresses ) ) ) {
		foreach ( $email_addresses as $email_address ) {
			$email_address = trim( $email_address );
			add_filter( 'wp_mail_content_type', 'srm_set_html_mail_content_type' );
			wp_mail( $email_address, $email_subject, $message_send, $headers );
			remove_filter( 'wp_mail_content_type', 'srm_set_html_mail_content_type' );
		}
	}

}

/**
 * Filter the mail content type.
 */
function srm_set_html_mail_content_type() {
    return 'text/html';
}
