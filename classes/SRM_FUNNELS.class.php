<?php
/**
 * FUNNELS
 *
 * Main class for managing Starfish Funnels instance
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0.0
 * @author  silvercolt45 <webmaster@silvercolt.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since  2.0.0
 */
class SRM_FUNNELS {

	/**
	 * SRM_FUNNELS constructor.
	 */
	public function __construct() {
	}

	/**
	 * The Freemius plan
	 *
	 * @param string $plan The freemius plan.
	 *
	 * @return bool
	 */
	public static function srm_restrict_add_new() {
		$total_funnels = self::srm_get_total_funnels();
		$restricted    = true;
		if ( $total_funnels <= SRM_PLAN_LIMIT || 0 === SRM_PLAN_LIMIT ) {
			$restricted = false;
		}
		return $restricted;
	}

	/**
	 * Get the current Freemius plan's limit.
	 *
	 * @param string $plan The freemius plan.
	 *
	 * @return int|string
	 */
	public static function srm_get_plan_limit( $plan ) {
        $lc_plan = strtolower( $plan );
		switch ( $lc_plan ) {
			case SRM_BUSINESS_PLAN:
                $limit = 5;
                break;
			case SRM_MARKETER_PLAN:
                $limit = 10;
                break;
			case SRM_WEBMASTER_PLAN:
                $limit = 0;
                break;
			default:
                $limit = 1;
                break;
		}
		return $limit;
	}

	/**
	 * Get all funnels
	 *
	 * @return array
	 */
	public static function srm_get_funnels( $status = 'published' ) {
		$args = array(
			'post_type'   => 'funnel',
			'status'      => $status,
			'numberposts' => -1,
		);
		return get_posts( $args );
	}

	/**
	 * Get total funnel count
	 *
	 * @return mixed
	 */
	public static function srm_get_total_funnels() {
		$args           = array(
			'post_type'      => array( 'funnel' ),
			'status'         => 'published',
			'posts_per_page' => -1,
		);
		$starfish_query = new WP_Query( $args );
		return $starfish_query->post_count;
	}

	/**
	 * Freemius plan restriction notice
	 *
	 * @param string  $plan The freemius plan.
	 * @param integer $limit The plan's limit.
	 *
	 * @return false|string
	 */
	public static function srm_admin_plan_restriction_notice( $plan, $limit ) {
		( empty( $plan ) || $plan == 'PLAN_TITLE' ) ? $plan = _x( 'FREE', 'The Free Subscription Plan', 'starfish' ) : $plan;
		$count                     = self::srm_get_total_funnels() - $limit;
		$current_plan_message      = _n( 'Your current plan (%1$s) only allows for %2$s published funnel at a time. %3$s', 'Your current plan (%1$s) only allows for %2$s published funnels at a time. %3$s', $count, 'starfish' );
		$message_c                 = sprintf( $current_plan_message, $plan, '<strong>' . $limit . '</strong>', '<a href="' . SRM_PLANS_URL . '" title="Upgrade Starfish Plan">' . __( 'Upgrade your plan!', 'starfish' ) . '</a>' );
		$auto_restriction_message  = _n( 'The newest %1$n funnel has been set to Draft status to bring your account within compliance.', 'The newest %1$n funnels have been set to Draft status to bring your account within compliance.', $count, 'starfish' );
		$message_r                 = sprintf( $auto_restriction_message, $count );
		return sprintf( '<div class="notice notice-warning"><p>%s<br/><br/><strong>%s</strong></p></div>', $message_c, $message_r );
	}

	/**
	 * Set all extra funnels past plan limit to draft status
	 *
	 * @param integer $limit
     *
     * @return integer
	 */
	public static function srm_force_extra_funnels_to_draft( $limit ) {
		global $wpdb;
		$count = self::srm_get_total_funnels() - $limit;
		if($count > 0 ) {
            $args           = array(
                'post_type'      => array( 'funnel' ),
                'orderby'        => 'created',
                'order'          => 'DESC',
                'posts_per_page' => $count,
            );
            $starfish_query = new WP_Query( $args );
            if ( $starfish_query->have_posts() ) {
                while ( $starfish_query->have_posts() ) {
                    $starfish_query->the_post();
                    $srm_post_id = get_the_ID();
                    $wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $srm_post_id ) );
                }
            }
            wp_reset_postdata();
        }
		return $count;
	}

	/**
	 * Get the funnel variables for building the Funnel template
	 *
	 * @param integer $funnel_id The funnel ID.
	 * @param string  $query_tracking_id The tracking ID query parameter.
	 * @param string  $query_feedback The feedback query parameter.
	 *
	 * @return array
	 */
	public static function srm_get_funnel_variables( $funnel_id, $query_tracking_id = '', $query_feedback = '' ) {

		$button_style = esc_html( get_post_meta( $funnel_id, '_srm_button_style', true ) );
		if ( $button_style == 'thumbs_outline' ) :
			$positive_class = 'far fa-thumbs-up';
			$negative_class = 'far fa-thumbs-down';
		elseif ( $button_style == 'thumbs_solid' ) :
			$positive_class = 'fas fa-thumbs-up';
			$negative_class = 'fas fa-thumbs-down';
		elseif ( $button_style == 'faces' ) :
			$positive_class = 'far fa-smile';
			$negative_class = 'far fa-frown';
		elseif ( $button_style == 'scircle' ) :
			$positive_class = 'fas fa-check-circle';
			$negative_class = 'fas fa-times-circle';
		else :
			$positive_class = 'far fa-thumbs-up';
			$negative_class = 'far fa-thumbs-down';
		endif;

		// Replace tokens.
		$blog_info_name  = get_bloginfo( 'name' );
		$yes_no_question = esc_html( get_post_meta( $funnel_id, '_srm_yn_question', true ) );
		$question        = wp_specialchars_decode( str_replace( '{site-name}', $blog_info_name, $yes_no_question ), ENT_QUOTES );
        $is_premium      = SRM_FREEMIUS::starfish_fs()->can_use_premium_code();

		// Build Funnel from template.
		return array(
			'id'                     => $funnel_id,
			'title'                  => get_the_title( $funnel_id ),
            'is_premium'             => $is_premium,
            'ajax_url'               => admin_url( 'admin-ajax.php' ),
			'feedback_nonce'         => wp_create_nonce( 'srm_feedback_nonce' ),
			'hide_branding'          => get_option( SRM_OPTION_HIDE_BRANDING, true ),
			'affiliate_url'          => get_option( SRM_OPTION_AFFILLIATE_URL, true ),
			'icons'                  => self::srm_get_icons(),
			'thumbnail'              => wp_get_attachment_image_src( get_post_thumbnail_id( $funnel_id ), 'full' ),
			'question'               => $question,
			'new_window'             => (bool) get_post_meta( $funnel_id, '_srm_open_new_window', true ),
			'destination_type'       => esc_html( get_post_meta( $funnel_id, '_srm_no_destination', true ) ),
            'destination_layout'     => esc_html( get_post_meta( $funnel_id, '_srm_multiple_destination_layout', true ) ),
			'destinations'           => get_post_meta( $funnel_id, '_srm_multi_desti', true ),
			'destination_url'        => wp_specialchars_decode( esc_url( get_post_meta( $funnel_id, '_srm_review_destination', true ) ) ),
			'auto_redirect_seconds'  => get_post_meta( $funnel_id, '_srm_review_auto_redirect', true ),
			'query_tracking_id'      => ( ! empty( $query_tracking_id ) ) ? esc_html( $query_tracking_id ) : null,
			'query_feedback'         => ( ! empty( $query_feedback ) ) ? esc_html( $query_feedback ) : null,
			'positive_class'         => $positive_class,
			'negative_class'         => $negative_class,
			'no_thank_you_msg'       => esc_html( get_post_meta( $funnel_id, '_srm_no_thank_you_msg', true ) ),
			'positive_review_prompt' => get_post_meta( $funnel_id, '_srm_yes_review_prompt', true ),
			'negative_review_prompt' => get_post_meta( $funnel_id, '_srm_no_review_prompt', true ),
			'ask_name'               => array(
				'enabled'  => get_post_meta( $funnel_id, '_srm_ask_name', true ),
				'required' => get_post_meta( $funnel_id, '_srm_ask_name_required', true ),
				'alert'    => esc_html__( 'Please enter your name.', 'starfish' ),
			),
			'ask_email'              => array(
				'enabled'  => get_post_meta( $funnel_id, '_srm_ask_email', true ),
				'required' => get_post_meta( $funnel_id, '_srm_ask_email_required', true ),
				'alert'    => esc_html__( 'Please enter a valid email.', 'starfish' ),
			),
			'ask_phone'              => array(
				'enabled'  => get_post_meta( $funnel_id, '_srm_ask_phone', true ),
				'required' => get_post_meta( $funnel_id, '_srm_ask_phone_required', true ),
				'alert'    => esc_html__( 'Please enter your phone.', 'starfish' ),
			),
			'placeholders'           => array(
				'review_text'           => esc_html__( 'Leave your review.', 'starfish' ),
				'name'                  => esc_html__( 'Your Name', 'starfish' ),
				'email'                 => esc_html__( 'Your Email', 'starfish' ),
				'phone'                 => esc_html__( 'Your Phone', 'starfish' ),
				'sending'               => esc_html__( 'Sending...' ),
				'public_review_text'    => esc_html__( 'Leave a Public Review', 'starfish' ),
				'min_review_alert_text' => esc_html__( 'You need to enter at least {0} characters', 'starfish' ),
				'affiliate_url'         => '<a href="https://starfish.reviews" title="Powered By Starfish">Powered By Starfish</a>',
				'skip_feedback_text'    => esc_html__( 'Skip this feedback and leave a public review.', 'starfish' ),
			),
			'button_text'            => esc_attr( get_post_meta( $funnel_id, '_srm_button_text', true ) ),
			'disable_review_gating'  => get_post_meta( $funnel_id, '_srm_disable_review_gating', true ),
			'skip_feedback_text'     => get_post_meta( $funnel_id, '_srm_skip_feedback_text', true ),
			'public_review_text'     => esc_html( get_post_meta( $funnel_id, '_srm_public_review_text', true ) ),
			'no_button_text'         => esc_attr( get_post_meta( $funnel_id, '_srm_button_text_no', true ) ),
			'srm_plugin_url'         => SRM_PLUGIN_URL,
			'labels'                 => array(
				'positive_response'    => esc_html__( 'Yes', 'starfish' ),
				'negative_response'    => esc_html__( 'No', 'starfish' ),
				'review_text'          => esc_html__( 'Your Review', 'starfish' ),
				'full_name'            => esc_html__( 'Full name', 'starfish' ),
				'email_address'        => esc_html__( 'Email Address', 'starfish' ),
				'phone_number'         => esc_html__( 'Phone Number', 'starfish' ),
				'required_fields_note' => esc_html__( 'indicates required fields', 'starfish' ),
			),
			'feedback'               => array(
				'review_text'   => esc_html__( 'Please provide a review.', 'starfish' ),
				'full_name'     => esc_html__( 'Please enter your name.', 'starfish' ),
				'email_address' => esc_html__( 'Please enter your email.', 'starfish' ),
				'phone_number'  => esc_html__( 'Please enter your phone.', 'starfish' ),
			),
			'placeholders'           => array(
				'review_text'   => esc_html__( 'Leave your review.', 'starfish' ),
				'full_name'     => esc_html__( 'Your Name', 'starfish' ),
				'email_address' => esc_html__( 'Your Email', 'starfish' ),
				'phone_number'  => esc_html__( 'Your phone.', 'starfish' ),
			),
		);

	}

	/**
	 * Get all the funnel icon configurations
	 *
	 * @return array
	 */
	public static function srm_get_icons() {
		return array(
			'GOOGLE'        => array(
				'name'  => 'Google',
				'class' => 'icon_google fab fa-google',
			),
			'FACEBOOK'      => array(
				'name'  => 'Facebook',
				'class' => 'icon_facebook fab fa-facebook-f',
			),
			'YELP'          => array(
				'name'  => 'Yelp',
				'class' => 'icon_yelp fab fa-yelp',
			),
			'TRIPADVISOR'   => array(
				'name'  => 'Tripadvisor',
				'class' => 'icon_tripadvisor fab fa-tripadvisor',
			),
			'AMAZON'        => array(
				'name'  => 'Amazon',
				'class' => 'icon_amazon fab fa-amazon',
			),
			'AUDIBLE'       => array(
				'name'  => 'Audible',
				'class' => 'icon_audible fab fa-audible',
			),
			'ITUNES'        => array(
				'name'  => 'iTunes',
				'class' => 'icon_itunes fab fa-itunes-note',
			),
			'APPLEAPPSTORE' => array(
				'name'  => 'AppleAppStore',
				'class' => 'icon_apple fab fa-apple',
			),
			'GOOGLEPLAY'    => array(
				'name'  => 'GooglePlay',
				'class' => 'icon_google fab fa-google-play',
			),
			'FOURSQUARE'    => array(
				'name'  => 'Foursquare',
				'class' => 'icon_foursquare fab fa-foursquare',
			),
			'WORDPRESS'     => array(
				'name'  => 'WordPress',
				'class' => 'icon_wordpress fab fa-wordpress',
			),
			'ETSY'          => array(
				'name'  => 'Etsy',
				'class' => 'icon_etsy fab fa-etsy',
			),
			'YOUTUBE'       => array(
				'name'  => 'YouTube',
				'class' => 'icon_youtube fab fa-youtube',
			),
		);
	}

	/**
	 * Get the URL of an attachment ID
	 *
	 * @param $icon_id
	 *
	 * @return mixed
	 */
	public static function get_icon_url( $icon_id ) {
		return wp_get_attachment_url( intval( $icon_id ) );
	}

	/**
	 * Get invalid funnel notice.
	 *
	 * @param integer $funnel_id The funnel ID.
	 *
	 * @return string $notice
	 */
	public static function srm_invalid_funnel_notice( $funnel_id ) {
		$title   = __( 'Starfish Notice', 'starfish' );
		$message = sprintf( __( 'This funnel (%1$s) is not valid or published yet!', 'starfish' ), $funnel_id );
	    return sprintf('<div class="srm-notice-message"><i class="fas fa-exclamation-circle fa-2x"> %s</i><br/>%s</div>', $title, $message);
	}

	/**
	 * The lastest Funnel ID.
	 *
	 * @return string $lastFunnelId
	 */
	public static function srm_get_last_funnel_id() {
		global $wpdb;
		$lastrowId = $wpdb->get_col( "SELECT ID FROM $wpdb->posts where post_type='funnel' and post_status='publish' ORDER BY post_date DESC " );
		if ( isset( $lastrowId[0] ) ) {
			$lastFunnelId = $lastrowId[0];
		} else {
			$lastFunnelId = '';
		}
		return $lastFunnelId;
	}

	public static function srm_enqueue_public_funnel_scripts( $funnel_id ) {
		$version = SRM_VERSION . '+' . wp_rand( 1, 99999999999 );
        wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'srm_funnel', SRM_PLUGIN_URL . '/css/starfish-funnel.css', null, $version, false );
		wp_enqueue_style( 'srm_funnel_stepper', 'https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css', null, $version, false );
		wp_enqueue_style( 'srm_bootstrap', SRM_PLUGIN_URL . '/css/srm-bootstrap.min.css', null, $version, false );
		wp_enqueue_style( 'srm_fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css', null, '5.15.2', false );
		wp_enqueue_script( 'srm_funnel_feedback_js', SRM_PLUGIN_URL . '/js/starfish-funnel-feedback.js', null, $version, false );
		wp_enqueue_script( 'srm_funnel_js_' . $funnel_id, SRM_PLUGIN_URL . '/js/starfish-funnel.js', null, $funnel_id . '-' . $version, false );
		wp_enqueue_script( 'srm_general_js', SRM_PLUGIN_URL . '/js/starfish-general.js', null, $version, false );
        wp_enqueue_script( 'srm_popper_js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js', null, $version, false );
        wp_enqueue_script( 'srm_bootstrap_js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js', null, $version, false );
        wp_enqueue_script( 'srm_stepper_js', 'https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js', null, $version, false);
	}

}

if ( class_exists( 'SRM_FUNNELS', true ) ) {
	return new SRM_FUNNELS();
}
