<?php

use Starfish\Logging as SRM_LOGGING;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Process Submitted Reviews
 **/
function srm_create_feedback()
{

    $funnel_id 				 = array_key_exists('funnel_id', $_POST) ? intval($_POST['funnel_id']) : null;
    $tracking_id 			 = array_key_exists('tracking_id', $_POST) ? sanitize_text_field($_POST['tracking_id']) : null;
    $decision 				 = array_key_exists('decision', $_POST) ? $_POST['decision'] : null;
    $funnel_destination_type = array_key_exists('destination_type', $_POST) ? sanitize_text_field($_POST['destination_type']) : null;
    $current_local_time 	 = date('j M Y g:i a', current_time('timestamp', 0));
    $destination_url 		 = array_key_exists('destination_url', $_POST) ? $_POST['destination_url'] : null;
    $destination_name 		 = array_key_exists('destination_name', $_POST) ? $_POST['destination_name'] : null;
    $auto_redirect 			 = array_key_exists('auto_redirect', $_POST) ? $_POST['auto_redirect'] : null;
	SRM_LOGGING::addEntry(
		array(
			'level'   => SRM_LOGGING::SRM_INFO,
			'action'  => 'Create Feedback',
			'message' => "funnel_id={{$funnel_id}},tracking_id={{$tracking_id}},decision={{$decision}},destination_type={{$funnel_destination_type}},current_local_time={{$current_local_time}},destination_url={{$destination_url}},destination_name={{$destination_name}},auto_redirect={{$auto_redirect}}",
			'code'    => 'SRM-A-WP-X-01',
		)
	);
    if (!check_ajax_referer('srm_feedback_nonce', 'security')) {
        echo wp_json_encode(["success" => false, "feedback_id" => null]);
        exit;
    } else {
        $defaults_feedback_arg = ['post_type' => 'starfish_feedback', 'post_title' => $current_local_time, 'post_status' => 'publish',];
        $feedback_id 		   = wp_insert_post($defaults_feedback_arg);
        if ($feedback_id !== 0 || !is_wp_error($feedback_id)) {
            add_post_meta($feedback_id, '_srm_destination_url', $destination_url);
            add_post_meta($feedback_id, SRM_META_FEEDBACK_ID, $decision);
            add_post_meta($feedback_id, SRM_META_FUNNEL_ID, $funnel_id);
            add_post_meta($feedback_id, SRM_META_TRACKING_ID, $tracking_id);
            add_post_meta($feedback_id, SRM_META_DESTI_TYPE, $funnel_destination_type);
            add_post_meta($feedback_id, SRM_META_DESTI_NAME, ($auto_redirect === 'imm') ? $destination_name : 'No');
            echo wp_json_encode(["success" => true, "feedback_id" => $feedback_id]);
        } else {
            echo wp_json_encode(["success" => false, "feedback_id" => null]);
        }
        wp_die();
    }

}

add_action('wp_ajax_srm-create-feedback', 'srm_create_feedback');
add_action('wp_ajax_nopriv_srm-create-feedback', 'srm_create_feedback');


/**
 * Update feedback post
 */
function srm_update_feedback()
{

    $feedback_id = intval((array_key_exists('feedback_id', $_POST)) ? $_POST['feedback_id'] : null);
    $funnel_id = intval((array_key_exists('funnel_id', $_POST)) ? $_POST['funnel_id'] : null);
    $send_email = rest_sanitize_boolean((array_key_exists('send_email', $_POST)) ? $_POST['send_email'] : null);
    $decision = rest_sanitize_boolean((array_key_exists('decision', $_POST)) ? $_POST['decision'] : null);
    $desti_name = sanitize_text_field((array_key_exists('destination_name', $_POST)) ? $_POST['destination_name'] : null);
    $desti_url = sanitize_text_field((array_key_exists('destination_url', $_POST)) ? $_POST['destination_url'] : null);
    $reviewer_name = sanitize_text_field((array_key_exists('reviewer_name', $_POST)) ? $_POST['reviewer_name'] : null);
    $reviewer_email = sanitize_text_field((array_key_exists('reviewer_email', $_POST)) ? $_POST['reviewer_email'] : null);
    $reviewer_phone = sanitize_text_field((array_key_exists('reviewer_phone', $_POST)) ? $_POST['reviewer_phone'] : null);
    $reviewer_text = sanitize_text_field((array_key_exists('reviewer_text', $_POST)) ? $_POST['reviewer_text'] : null);
    $tracking_id = sanitize_text_field((array_key_exists('tracking_id', $_POST)) ? $_POST['tracking_id'] : null);

    if (!check_ajax_referer('srm_feedback_nonce', 'security')) {
        echo wp_json_encode(["success" => false, "feedback_id" => $feedback_id]);
        wp_die();
    } else {
        if (!empty($desti_url)) {
            update_post_meta($feedback_id, SRM_META_DESTI_URL, $desti_url);
        }
        if (!empty($desti_name) || $desti_name !== '') {
            update_post_meta($feedback_id, SRM_META_DESTI_NAME, $desti_name);
        }
        if (!empty($reviewer_name)) {
            update_post_meta($feedback_id, SRM_META_REVIEWER_NAME, $reviewer_name);
        }
        if (!empty($reviewer_email)) {
            update_post_meta($feedback_id, SRM_META_REVIEWER_EMAIL, $reviewer_email);
        }
        if (!empty($reviewer_phone)) {
            update_post_meta($feedback_id, SRM_META_REVIEWER_PHONE, $reviewer_phone);
        }
        if (!empty($reviewer_text)) {
            $args = ['ID' => $feedback_id, 'post_content' => $reviewer_text,];
            wp_update_post($args);
        }
        // Send email if negative feedback.
        if (!$decision && $send_email) {
            $review_message = sanitize_text_field($reviewer_text);
            srm_send_feedback_email($funnel_id, $review_message, $tracking_id, $feedback_id);
        }
        echo wp_json_encode(["success" => true, "feedback_id" => $feedback_id]);
        wp_die();
    }

}

add_action('wp_ajax_srm-update-feedback', 'srm_update_feedback');
add_action('wp_ajax_nopriv_srm-update-feedback', 'srm_update_feedback');

function srm_send_feedback_email($funnel_id, $review_message, $tracking_id, $feedback_id)
{

    $admin_email = get_option('admin_email');
    $sitename 	 = get_option('blogname');

    $funnel_name = get_the_title($funnel_id);
    $email_subject = stripslashes(get_option('srm_email_subject'));
    $search_subject = [];
    $replace_subject = [];
    $search_subject[] = '{tracking-id}';
    $replace_subject[] = $tracking_id;
    $search_subject[] = '{funnel-name}';
    $replace_subject[] = $funnel_name;
    $email_subject = str_replace($search_subject, $replace_subject, $email_subject);
    $email_subject = wp_specialchars_decode($email_subject, ENT_QUOTES);

    $reviewer_name = esc_html(get_post_meta($feedback_id, SRM_META_REVIEWER_NAME, true));
    $reviewer_email = esc_html(get_post_meta($feedback_id, SRM_META_REVIEWER_EMAIL, true));
    $reviewer_phone = esc_html(get_post_meta($feedback_id, SRM_META_REVIEWER_PHONE, true));

    // process email message
    $message = get_option('srm_email_template');
    $search = [];
    $replace = [];
    $search[] = '{admin-email}';
    $replace[] = $admin_email;
    $search[] = '{site-name}';
    $replace[] = $sitename;
    $search[] = '{funnel-name}';
    $replace[] = $funnel_name;
    $search[] = '{tracking-id}';
    $replace[] = $tracking_id;
    $search[] = '{reviewer-name}';
    $replace[] = $reviewer_name;
    $search[] = '{reviewer-email}';
    $replace[] = $reviewer_email;
    $search[] = '{reviewer-phone}';
    $replace[] = $reviewer_phone;
    $search[] = '{review-message}';
    $replace[] = $review_message;
    $message = str_replace($search, $replace, $message);
    $email_message = stripslashes($message);

    // process email addresses
    $srm_email_feedback = esc_html(get_post_meta($funnel_id, SRM_META_EMAIL_FEEDBACK, true));
    $search = [];
    $replace = [];
    $search[] = '{admin-email}';
    $replace[] = $admin_email;
    $get_email_addresses = str_replace($search, $replace, $srm_email_feedback);
    $email_addresses = explode(',', $get_email_addresses);

    $srm_email_from_name = esc_html(stripslashes(get_option('srm_email_from_name')));
    $search = [];
    $replace = [];
    $search[] = '{site-name}';
    $replace[] = $sitename;
    $srm_email_from_name = str_replace($search, $replace, $srm_email_from_name);

    $srm_email_from_email = esc_html(get_option('srm_email_from_email'));
    $search = [];
    $replace = [];
    $search[] = '{admin-email}';
    $replace[] = $admin_email;
    $srm_email_from_email = str_replace($search, $replace, $srm_email_from_email);

    // set reply-to email address
    $srm_email_reply_to_email = '';
    if (is_email($reviewer_email)) {
        $srm_email_reply_to_email = $reviewer_email;
    } elseif (is_email($tracking_id) && (get_option('srm_replay_to_email') == 'yes')) {
        $srm_email_reply_to_email = $tracking_id;
    } elseif (is_email($srm_email_from_email)) {
        $srm_email_reply_to_email = $srm_email_from_email;
    }

    $headers[] = 'MIME-Version: 1' . "\r\n";
    $headers[] = 'Content-Type: text/html; charset=UTF-8' . "\r\n";

    //get the full domain
    $urlparts = wp_parse_url(site_url());
    $domain = $urlparts ['host'];

    //get the TLD and domain
    $domainparts = explode(".", $domain);
    $domain = $domainparts[count($domainparts) - 2] . "." . $domainparts[count($domainparts) - 1];

    if ((!empty($srm_email_from_name)) && (!empty($srm_email_from_email))) {
        $headers[] = 'From: ' . wp_specialchars_decode(esc_html($srm_email_from_name), ENT_QUOTES) . ' <' . apply_filters('starfish_notification_email_header_from_email', $srm_email_from_email) . ">\r\n";
    } else {
        $headers[] = 'From: no-reply@' . $domain . ' <no-reply@' . $domain . ">\r\n";
    }

    if (!empty($srm_email_reply_to_email)) {
        $headers[] = 'Reply-To: <' . $srm_email_reply_to_email . '>';
    }

    if (!empty(array_filter($email_addresses))) {
        foreach ($email_addresses as $email_address) {
            $email_address = trim($email_address);

            add_filter('wp_mail_content_type', 'srm_set_html_mail_content_type');
            wp_mail($email_address, $email_subject, html_entity_decode($email_message), $headers);
            remove_filter('wp_mail_content_type', 'srm_set_html_mail_content_type');
        }
    }
}

/**
 * Filter the mail content type.
 */
function srm_set_html_mail_content_type()
{
    return 'text/html';
}
