<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="wrap">
<?php
if ( isset($_POST['srm_settings_nonce']) && (! isset( $_POST['srm_settings_nonce'] ) || ! wp_verify_nonce( $_POST['srm_settings_nonce'], 'srm_settings_action' ) ) ) {
   //Verifiy not match..
   starfish_notice_data_nonce_verify_required();
}elseif( isset( $_POST['srm_settings_nonce'] ) && ($_POST['srm_review_destination'] == '')){
   starfish_notice_data_review_destination_url_required();
}else {
   if( !current_user_can( 'manage_options' ) ){
     return;
   }
   if(isset($_POST['srm_settings_submit'])){
       update_option('srm_review_destination', esc_url($_POST['srm_review_destination']));
       update_option('srm_destination_name', sanitize_text_field($_POST['srm_destination_name']));
       update_option('srm_to_email', sanitize_text_field($_POST['srm_to_email']));
       if(intval(get_option('srm_funnel_id'))){
         $funnel_id = get_option('srm_funnel_id');
         update_post_meta( $funnel_id, '_srm_review_destination', esc_url($_POST['srm_review_destination']) );
         update_post_meta( $funnel_id, '_srm_email_feedback', sanitize_text_field($_POST['srm_to_email']) );
       }
       if(isset($_POST['srm_show_powered_by'])){
         update_option('srm_show_powered_by', sanitize_text_field($_POST['srm_show_powered_by']));
       }else{
         update_option('srm_show_powered_by', 'no');
       }
       if(isset($_POST['srm_clean_on_deactive'])){
         update_option('srm_clean_on_deactive', sanitize_text_field($_POST['srm_clean_on_deactive']));
       }else{
         update_option('srm_clean_on_deactive', 'no');
       }
       update_option('srm_affiliate_url', sanitize_text_field($_POST['srm_affiliate_url']));
       update_option('srm_email_from_name', sanitize_text_field($_POST['srm_email_from_name']));
       update_option('srm_email_from_email', sanitize_text_field($_POST['srm_email_from_email']));
       update_option('srm_email_subject', sanitize_text_field($_POST['srm_email_subject']));
       update_option('srm_email_template', wp_kses_post($_POST['srm_email_template']));
       starfish_notice_data_successfully_saved();
   }
}
?>
 <div class="srm_settings_review_header">
     <div class="srm_settings_left_box">
       <h2><?php echo __( 'Starfish Reviews Lite', 'starfish' ); ?></h2>
       <p class="plugin-description"><?php echo __( '<strong>How to use this plugin:</strong> In the "Review Destination" field, put the URL where you\'d like to receive reviews, then save changes. Now send the "Review Page URL" to your clients, followers, users, etc, asking for feedback. They\'ll be asked if they like your product or service. Those who do will be sent to the "Review Destination" to leave you a review. The ones who don\'t can submit their feedback and it will be emailed to you via the "Email Settings".', 'starfish' ); ?></p>
     </div><!-- srm_settings_left_box -->
     <div class="srm_settings_right_box">
        <div class="srm_promotion_box">
            <img src="<?php echo SRM_LITE_PLUGIN_URL.'/img/starfish-5-star-reviews.png'; ?>" alt="Starfish 5 Star Reviews" />
            <div class="srm_promotion_data">
              <h4><?php echo __( 'Want More?', 'starfish' ); ?></h4>
              <p><strong><?php echo __( 'Checkout our Business and Webmaster versions', 'starfish' ); ?></strong></p>
              <ul>
                <li><?php echo __( 'Priority Support', 'starfish' ); ?></li>
                <li><?php echo __( 'Multiple Destinations!', 'starfish' ); ?></li>
                <li><?php echo __( 'Auto-forward', 'starfish' ); ?></li>
                <li><?php echo __( 'Disable “Gating”', 'starfish' ); ?></li>
                <li><?php echo __( 'Display Logo', 'starfish' ); ?></li>
                <li><?php echo __( 'Overall Rating', 'starfish' ); ?></li>
                <li><?php echo __( 'Analytics', 'starfish' ); ?></li>
                <li><?php echo __( 'Customize Wording', 'starfish' ); ?></li>
                <li><?php echo __( 'Insert via Shortcode', 'starfish' ); ?></li>
                <li><?php echo __( 'Customize URL', 'starfish' ); ?></li>
                <li><?php echo __( 'Manage Feedback', 'starfish' ); ?></li>
                <li><?php echo __( 'Vote on New Features', 'starfish' ); ?></li>
                <li><?php echo __( 'More Funnels', 'starfish' ); ?></li>
                <li><?php echo __( 'Change the Prompts', 'starfish' ); ?></li>
              </ul>
              <a class="srm_btn_learn_more button button-primary" href="https://starfishwp.com/" target="_blank"><?php echo __( 'Learn more', 'starfish' ); ?></a>
            </div><!-- srm_promotion_data -->
            <a class="srm_btn_get_support" href="https://wordpress.org/support/plugin/starfish-reviews" target="_blank"><?php echo __( 'Get Support for Reviews Lite', 'starfish' ); ?></a>
        </div><!-- srm_promotion_box -->
     </div><!-- srm_settings_right_box -->
 </div><!-- srm_settings_review_header -->
 <div class="srm_settings_review_header_overlay">
   &nbsp;
 </div><!-- srm_settings_review_header_overlay -->

 <div class="srm_settings_review_form">
   <div class="srm_settings_left_box">
     <form method="post" action="admin.php?page=starfish-reviews" novalidate="novalidate">
     	<input type="hidden" name="srm_settings_page" value="srm">
     	<?php wp_nonce_field( 'srm_settings_action', 'srm_settings_nonce' ); ?>
     	<?php wp_referer_field(); ?>
     	<table class="form-table">
        <tr>
          <th scope="row"><label for="srm_review_slug"><?php echo __( 'Review Page URL', 'starfish' ); ?></label></th>
          <td><input disabled="disabled" name="srm_review_slug" type="text" id="srm_review_slug" value="<?php echo get_permalink(get_option('srm_funnel_id')); ?>" class="regular-text">
            <a class="funnel_link_open" href="<?php echo get_permalink(get_option('srm_funnel_id')); ?>" target="_blank"><span class="dashicons dashicons-external"></span></a>
            <p class="description" id="srm_review_slug-description"><?php echo __( 'Re-save WP Settings>Permalink if your funnel goes to a 404 page.', 'starfish' ); ?></p></td>
        </tr>
        <tr>
          <th scope="row"><label for="srm_review_destination"><?php echo __( 'Review Destination<sup style="color:red;">*</sup>', 'starfish' ); ?></label></th>
          <td><input name="srm_review_destination" type="text" id="srm_review_destination" value="<?php echo esc_url(get_option('srm_review_destination')); ?>" class="regular-text">
            <p class="description" id="srm_review_destination-description"><?php echo __( 'This is where people with positive feedback will be taken to leave a review.', 'starfish' ); ?></p></td>
        </tr>
        <tr>
    			<th scope="row"><label for="srm_destination_name"><?php echo __( 'Destination Name', 'starfish' ); ?></label></th>
    			<td><input name="srm_destination_name" type="text" id="srm_destination_name" value="<?php echo esc_html(get_option('srm_destination_name')); ?>" class="regular-text">
    			<p class="description" id="srm_destination_name-description"><?php echo __( 'People with positive feedback will be told "Excellent! Please rate us 5-stars on (Destination Name here)". Eg. if your "Review Destination" above links to Facebook, put "Facebook" here.', 'starfish' ); ?></p></td>
    		</tr>
        <tr>
    		<?php
        $srm_show_powered_by = '';
    		if(get_option('srm_show_powered_by')){
    			$srm_show_powered_by = get_option('srm_show_powered_by');
    		}
    		?>
    		<th scope="row"><?php echo __( 'Powered by Starfish', 'starfish' ); ?></th>
    		<td><fieldset><legend class="screen-reader-text"><span><?php echo __( 'Powered by Starfish', 'starfish' ); ?></span></legend><label for="srm_show_powered_by">
    		<input name="srm_show_powered_by" type="checkbox" id="srm_show_powered_by" <?php checked( 'yes', $srm_show_powered_by, true ); ?> value="yes">
    		<?php echo __( 'Show my appreciation and support for this plugin by displaying a small "Powered by Starfish" link at the bottom of the funnel.', 'starfish' ); ?></label>
    		</fieldset></td>
    		</tr>
        <tr>
          <th scope="row"><label for="srm_affiliate_url"><?php echo __( 'Affiliate URL', 'starfish' ); ?></label></th>
          <td><input name="srm_affiliate_url" type="text" id="srm_affiliate_url" value="<?php echo esc_url(get_option('srm_affiliate_url')); ?>" class="regular-text">
            <p class="description" id="srm_affiliate_url-description"><?php echo __( 'Override "Powered by Starfish" link URL with <a href="https://starfishwp.com/affiliate/?sub=creatives" target="_blank">your affiliate URL</a>.', 'starfish' ); ?></p></td>
        </tr>
        <tr>
    		<?php
        $srm_clean_on_deactive = '';
    		if(get_option('srm_clean_on_deactive')){
    			$srm_clean_on_deactive = get_option('srm_clean_on_deactive');
    		}
    		?>
    		<th scope="row"><?php echo __( 'Deactivation', 'starfish' ); ?></th>
    		<td><fieldset><legend class="screen-reader-text"><span><?php echo __( 'Deactivation', 'starfish' ); ?></span></legend><label for="srm_clean_on_deactive">
    		<input name="srm_clean_on_deactive" type="checkbox" id="srm_clean_on_deactive"  <?php checked( 'yes', $srm_clean_on_deactive, true ); ?> value="yes">
    		<?php echo __( 'Delete all settings on deactivation. DO NOT use this option, unless you want to remove ALL Starfish Reviews settings and data.', 'starfish' ); ?></label>
    		</fieldset></td>
    		</tr>
        <tr><th colspan="2"><h2><?php echo __( 'Email Settings', 'starfish' ); ?></h2></th></tr>
        <tr>
    			<th scope="row"><label for="srm_to_email"><?php echo __( 'To Email', 'starfish' ); ?></label></th>
    			<td><input name="srm_to_email" type="text" id="srm_to_email" value="<?php echo esc_html(get_option('srm_to_email')); ?>" class="regular-text">
    			<p class="description" id="srm_to_email-description"><?php echo __( 'Email address(es) to send negative feedback to. You can add comma separated multiple email addresses. Shortcode {admin-email} default to the email set in WP Settings->General', 'starfish' ); ?></p></td>
    		</tr>
        <tr>
    			<th scope="row"><label for="srm_email_from_name"><?php echo __( 'From Name', 'starfish' ); ?></label></th>
    			<td><input name="srm_email_from_name" type="text" id="srm_email_from_name" value="<?php echo esc_html(get_option('srm_email_from_name')); ?>" class="regular-text">
    			<p class="description" id="srm_email_from_name-description"><?php echo __( 'The name the email will appear to be from. Supports shortcode {site-name} to use the site\'s name.', 'starfish' ); ?></p></td>
    		</tr>
        <tr>
          <th scope="row"><label for="srm_email_from_email"><?php echo __( 'From Email', 'starfish' ); ?></label></th>
          <td><input name="srm_email_from_email" type="text" id="srm_email_from_email" value="<?php echo esc_html(get_option('srm_email_from_email')); ?>" class="regular-text">
          <p class="description" id="srm_email_from_email-description"><?php echo __( 'Email address the message will appear to be from. Supports shortcode {admin-email} to use the site\'s admin email.', 'starfish' ); ?></p></td>
        </tr>
    		<tr>
    			<th scope="row"><label for="srm_email_subject"><?php echo __( 'Email Subject', 'starfish' ); ?></label></th>
    			<td><input name="srm_email_subject" type="text" id="srm_email_subject" value="<?php echo esc_html(get_option('srm_email_subject')); ?>" class="regular-text">
    			<p class="description" id="srm_email_subject-description"><?php echo __( 'Subject of the email.', 'starfish' ); ?></p></td>
    		</tr>
        <tr>
    			<th scope="row"><label for="srm_email_template"><?php echo __( 'Message Template', 'starfish' ); ?></label></th>
    			<td><textarea name="srm_email_template" id="srm_email_template" rows="5" cols="60"  placeholder="<?php echo __( '{reveiw-message}', 'starfish' ); ?>"><?php echo esc_html(get_option('srm_email_template')); ?></textarea>
    			<p class="description" id="srm_email_template-description"><?php echo __( 'Contents of the message. Make sure to include shortcode {review-message}.', 'starfish' ); ?></p></td>
    		</tr>
        <tr><th>&nbsp;</th><td><p class="submit"><input type="submit" name="srm_settings_submit" id="srm_settings_submit" class="button button-primary" value="Save Changes"></p></td></tr>
     	</table>
     </form>
   </div><!-- srm_settings_left_box -->
   <div class="srm_settings_right_box">
   </div><!-- srm_settings_right_box -->
 </div><!-- srm_settings_review_form -->
</div><!-- wrap -->
