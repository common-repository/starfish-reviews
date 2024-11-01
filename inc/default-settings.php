<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
update_option('srm_show_powered_by', 'no');
update_option('srm_review_slug', 'review');
update_option('srm_yes_redirect_seconds', '0');
$srm_default_yn_question = __( 'Would you recommend {site-name}?', 'starfish' );
update_option('srm_default_yn_question', $srm_default_yn_question);
$srm_default_submit_button = __( 'Submit Review', 'starfish' );
update_option('srm_default_submit_button', $srm_default_submit_button);
$srm_yes_review_prompt = __( '<h3>Excellent! Please Rate us 5-stars on {destination-name}</h3>...and leave a helpful review.', 'starfish' );
update_option('srm_default_review_prompt', $srm_yes_review_prompt);
$srm_no_review_prompt = __( 'We\'re sorry we didn\'t meet expectations. How can we do better in the future?', 'starfish' );
update_option('srm_default_no_review_prompt', $srm_no_review_prompt);
$srm_msg_no_thank_you = __( 'Thanks for your feedback. We\'ll review it and work to improve.', 'starfish' );
update_option('srm_msg_no_thank_you', $srm_msg_no_thank_you);
update_option('srm_default_feedback_email', '{admin-email}');
update_option('srm_email_from_name', '{site-name}');
update_option('srm_email_from_email', '{admin-email}');
update_option('srm_to_email', '{admin-email}');
$srm_email_subject = __( 'Feedback from Starfish Reviews', 'starfish' );
update_option('srm_email_subject', $srm_email_subject);
$srm_default_email_template = __( 'Message: {review-message}', 'starfish' );
update_option('srm_email_template', $srm_default_email_template);
update_option('srm_clean_on_deactive', 'no');
$active_date = time();
update_option('srm_active_date', $active_date);
$existing_funnel_id = srm_check_already_generated_funnel();
if($existing_funnel_id > 0){
  update_option('srm_funnel_id', $existing_funnel_id);
  update_post_meta( $existing_funnel_id, '_srm_yn_question', $srm_default_yn_question );
  update_post_meta( $existing_funnel_id, '_srm_button_text', $srm_default_submit_button );
  update_post_meta( $existing_funnel_id, '_srm_yes_review_prompt', $srm_yes_review_prompt );
  update_post_meta( $existing_funnel_id, '_srm_no_review_prompt', $srm_no_review_prompt );
  update_post_meta( $existing_funnel_id, '_srm_no_thank_you_msg', $srm_msg_no_thank_you );
}else{
  $default_funnel_arg = array(
          'post_type'      => 'funnel',
          'post_title'     => 'Funnel',
          'post_status'    => 'publish'
        );
  if($funnel_id = wp_insert_post( $default_funnel_arg )) {
    update_option('srm_funnel_id', $funnel_id);
    update_post_meta( $funnel_id, '_srm_yn_question', $srm_default_yn_question );
    update_post_meta( $funnel_id, '_srm_button_text', $srm_default_submit_button );
    update_post_meta( $funnel_id, '_srm_yes_review_prompt', $srm_yes_review_prompt );
    update_post_meta( $funnel_id, '_srm_no_review_prompt', $srm_no_review_prompt );
    update_post_meta( $funnel_id, '_srm_no_thank_you_msg', $srm_msg_no_thank_you );
  }
}

function srm_check_already_generated_funnel(){
	$args = array(
		'post_type'  => 'funnel',
		'post_status' => 'any',
		'orderby' => 'post_date',
		'order' => 'ASC',
		'posts_per_page' => 1,
	);

	$funnel_id = 0;
	$funnel_query = new WP_Query( $args );
	if ( $funnel_query->have_posts() ) {
		while ( $funnel_query->have_posts() ) {
			$funnel_query->the_post();
			$funnel_id = get_the_ID();
		}
	}

	wp_reset_postdata();
	return $funnel_id;
}
