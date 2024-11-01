<?php
if (!defined('ABSPATH')) {
	exit;
}

if (get_option(SRM_OPTION_CLEAN_ON_DEACTIVATE) === 'yes') {
	delete_option('srm_feedback_slug');
	delete_option('srm_all_funnels');
	delete_option('srm_branding');
	delete_option('srm_yes_redirect_seconds');
	delete_option('srm_default_yn_question');
	delete_option('srm_default_review_prompt');
	delete_option('srm_default_submit_button_no');
	delete_option('srm_default_submit_button');
	delete_option('srm_default_no_review_prompt');
	delete_option('srm_msg_no_thank_you');
	delete_option('srm_default_feedback_email');
	delete_option('srm_email_from_name');
	delete_option('srm_email_from_email');
	delete_option('srm_replay_to_email');
	delete_option('srm_email_subject');
	delete_option('srm_email_template');
	delete_option('srm_affiliate_url');
	delete_option('srm_default_public_review_text');
	delete_option('srm_default_skip_feedback_text');
	delete_option('srm_migrations');
	delete_option('srm_install_date');
	delete_option('srm_review_scrape_cron');
	delete_option('srm_clean_on_deactive');
	delete_option('srm_funnel_archives');
	delete_option('srm_richreviews_migrated');
	// Clear transients
	delete_option('_transient_srm_new_install');
	delete_option('_transient_timeout_srm_new_install');
	// Clear post data
	srm_clear_all_post_data();
	srm_clear_all_reviews_data();
}

function srm_clear_all_post_data()
{
	$args = ['post_type' => ['funnel', 'starfish_feedback', 'collection'], 'post_status' => 'any', 'posts_per_page' => -1,];
	$posts = get_posts($args);
	if (!empty($posts)) {
		foreach ($posts as $post) {
			wp_delete_post($post->ID, true);
		}
		wp_reset_postdata();
	}
}

function srm_clear_all_reviews_data()
{
	global $wpdb;
	$tables = [$wpdb->prefix . 'srm_reviews', $wpdb->prefix . 'srm_reviews_last_summary', $wpdb->prefix . 'srm_reviews_profiles',];
	$views = [$wpdb->prefix . 'srm_view_reviews_profiles',];
	foreach ($tables as $table) {
		$wpdb->query("DROP TABLE IF EXISTS $table");
	}
	foreach ($views as $view) {
		$wpdb->query("DROP VIEW IF EXISTS $view");
	}
}
