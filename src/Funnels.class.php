<?php

namespace Starfish;

use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\FunnelIcons as SRM_FUNNEL_ICONS;
use Starfish\Logging as SRM_LOGGING;
use WP_Query;

/**
 * FUNNELS
 *
 * Main class for managing Starfish Funnels instance
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1.0
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since      2.0
 */
class Funnels
{
    public function __construct()
    {
    }

    /**
     * The Freemius plan
     *
     * @return bool
     */
    public static function srm_restrict_add_new()
    {
        $total_funnels = self::srm_get_total_funnels();
        $restricted    = true;
        $plan_limits   = SRM_FREEMIUS::srm_get_plan_limit();
        if ($total_funnels <= $plan_limits['funnels'] || null === $plan_limits['funnels']) {
            $restricted = false;
        }

        return $restricted;
    }

    /**
     * Get all funnels
     *
     * @return array
     */
    public static function srm_get_funnels($status = 'published')
    {
        $args = array(
            'post_type'   => 'funnel',
            'status'      => $status,
            'numberposts' => -1,
        );

        return get_posts($args);
    }

    /**
     * Get total funnel count
     *
     * @return mixed
     */
    public static function srm_get_total_funnels()
    {
        $args           = array(
            'post_type'      => array( 'funnel' ),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        $starfish_query = new WP_Query($args);

        return $starfish_query->post_count;
    }

    /**
     * Set all extra funnels past plan limit to draft status
     *
     * @param mixed $limit
     *
     * @return mixed|int
     */
    public static function srm_force_extra_funnels_to_draft($limit)
    {
        global $wpdb;
        $violations = 0; // Account for unlimited plans
        if (empty($limit) && $limit !== 0) {
            return $violations;
        }
        $count      = self::srm_get_total_funnels();
        $violations = max($count - $limit, 0);
        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Funnel Limit Violations:',
                'message' => $violations . '/'. $limit,
                'code'    => 'SRM_C_FEFD_X_01',
            )
        );
        if ($violations > 0) {
            $args           = array(
                'post_type'      => array( 'funnel' ),
                'orderby'        => 'modified',
                'order'          => 'DESC',
                'posts_per_page' => $violations,
            );
            $starfish_query = new WP_Query($args);
            if ($starfish_query->have_posts()) {
                while ($starfish_query->have_posts()) {
                    $starfish_query->the_post();
                    $srm_post_id = get_the_ID();
                    $updated     = $wpdb->update($wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $srm_post_id ));
                    if (false === $updated) {
                        SRM_LOGGING::addEntry(
                            array(
                                'level'   => SRM_LOGGING::SRM_ERROR,
                                'action'  => 'SET_FUNNELS_TO_DRAFT',
                                'message' => $wpdb->last_error(),
                                'code'    => 'SRM-F-FF2D-D',
                            )
                        );
                    }
                }
            }
            wp_reset_postdata();
        }

        return max($violations, 0);
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
    public static function srm_get_funnel_variables($funnel_id, $query_tracking_id = '', $query_feedback = '')
    {
        $button_style = esc_html(get_post_meta($funnel_id, '_srm_button_style', true));
        if ($button_style == 'thumbs_outline') :
            $positive_class = 'far fa-thumbs-up';
            $negative_class = 'far fa-thumbs-down';
        elseif ($button_style == 'thumbs_solid') :
            $positive_class = 'fas fa-thumbs-up';
            $negative_class = 'fas fa-thumbs-down';
        elseif ($button_style == 'faces') :
            $positive_class = 'far fa-smile';
            $negative_class = 'far fa-frown';
        elseif ($button_style == 'scircle') :
            $positive_class = 'fas fa-check-circle';
            $negative_class = 'fas fa-times-circle';
        else :
            $positive_class = 'far fa-thumbs-up';
            $negative_class = 'far fa-thumbs-down';
        endif;

        // Replace tokens.
        $blog_info_name  = get_bloginfo('name');
        $yes_no_question = esc_html(get_post_meta($funnel_id, '_srm_yn_question', true));
        $question        = wp_specialchars_decode(str_replace('{site-name}', $blog_info_name, $yes_no_question), ENT_QUOTES);
        $is_premium      = SRM_FREEMIUS::starfish_fs()->can_use_premium_code();

        // Build Funnel from template.
        return array(
            'plugin_url'             => SRM_PLUGIN_URL,
            'id'                     => $funnel_id,
            'title'                  => get_the_title($funnel_id),
            'is_premium'             => $is_premium,
            'ajax_url'               => admin_url('admin-ajax.php'),
            'feedback_nonce'         => wp_create_nonce('srm_feedback_nonce'),
            // TODO: give custom name to feedback nonce
            'hide_branding'          => get_option(SRM_OPTION_HIDE_BRANDING, false),
            'affiliate_url'          => get_option(SRM_OPTION_AFFILIATE_URL, SRM_PLANS_URL),
            'icons'                  => self::srm_get_icons(),
            'thumbnail'              => wp_get_attachment_image_src(get_post_thumbnail_id($funnel_id), 'full'),
            'question'               => $question,
            'new_window'             => (bool) get_post_meta($funnel_id, '_srm_open_new_window', true),
            'destination_type'       => esc_html(get_post_meta($funnel_id, '_srm_no_destination', true)),
            'destination_layout'     => esc_html(get_post_meta($funnel_id, '_srm_multiple_destination_layout', true)),
            'destinations'           => get_post_meta($funnel_id, '_srm_multi_desti', true),
            'destination_url'        => wp_specialchars_decode(esc_url(get_post_meta($funnel_id, '_srm_review_destination', true))),
            'auto_redirect_seconds'  => get_post_meta($funnel_id, '_srm_review_auto_redirect', true),
            'query_tracking_id'      => (!empty($query_tracking_id)) ? esc_html($query_tracking_id) : null,
            'query_feedback'         => (!empty($query_feedback)) ? esc_html($query_feedback) : null,
            'positive_class'         => $positive_class,
            'negative_class'         => $negative_class,
            'no_button_text'         => esc_html(get_post_meta($funnel_id, '_srm_button_text_no', true)),
            'no_thank_you_msg'       => esc_html(get_post_meta($funnel_id, '_srm_no_thank_you_msg', true)),
            'positive_review_prompt' => get_post_meta($funnel_id, '_srm_yes_review_prompt', true),
            'negative_review_prompt' => get_post_meta($funnel_id, '_srm_no_review_prompt', true),
            'embedded_dynamic_sizing' => get_post_meta($funnel_id, '_srm_funnel_embed_dynamic_sizing', true),
            'embedded_target_origin' => get_post_meta($funnel_id, '_srm_funnel_embed_target_origin', true),
            'ask_name'               => array(
                'enabled'  => get_post_meta($funnel_id, '_srm_ask_name', true),
                'required' => get_post_meta($funnel_id, '_srm_ask_name_required', true),
                'alert'    => esc_html__('Please enter your name.', 'starfish'),
            ),
            'ask_email'              => array(
                'enabled'  => get_post_meta($funnel_id, '_srm_ask_email', true),
                'required' => get_post_meta($funnel_id, '_srm_ask_email_required', true),
                'alert'    => esc_html__('Please enter a valid email.', 'starfish'),
            ),
            'ask_phone'              => array(
                'enabled'  => get_post_meta($funnel_id, '_srm_ask_phone', true),
                'required' => get_post_meta($funnel_id, '_srm_ask_phone_required', true),
                'alert'    => esc_html__('Please enter your phone.', 'starfish'),
            ),
            'placeholders'           => array(
                // TODO: Turn into configurable options.
                'feedback_text'         => esc_html__('Leave your feedback.', 'starfish'),
                'full_name'             => esc_html__('Your Name', 'starfish'),
                'email_address'         => esc_html__('Your Email', 'starfish'),
                'phone_number'          => esc_html__('Your Phone', 'starfish'),
                'sending'               => esc_html__('Sending...'),
                'public_review_text'    => esc_html__('Leave a Public Review', 'starfish'),
                'min_review_alert_text' => esc_html__('You need to enter at least {0} characters', 'starfish'),
                'affiliate_url'         => '<a href="https://starfishwp.com" title="Powered By Starfish">Powered By Starfish</a>',
                'skip_feedback_text'    => esc_html__('Skip this feedback and leave a public review.', 'starfish'),
            ),
            'button_text'            => esc_attr(get_post_meta($funnel_id, '_srm_button_text', true)),
            'disable_review_gating'  => get_post_meta($funnel_id, '_srm_disable_review_gating', true),
            'skip_feedback_text'     => get_post_meta($funnel_id, '_srm_skip_feedback_text', true),
            'public_review_text'     => esc_html(get_post_meta($funnel_id, '_srm_public_review_text', true)),
            'no_button_text'         => esc_attr(get_post_meta($funnel_id, '_srm_button_text_no', true)),
            'srm_plugin_url'         => SRM_PLUGIN_URL,
            'labels'                 => array(
                // TODO: Turn into configurable options.
                'positive_response'    => esc_html__('Yes', 'starfish'),
                'negative_response'    => esc_html__('No', 'starfish'),
                'feedback_text'        => esc_html__('Your Feedback', 'starfish'),
                'full_name'            => esc_html__('Full Name', 'starfish'),
                'email_address'        => esc_html__('Email Address', 'starfish'),
                'phone_number'         => esc_html__('Phone Number', 'starfish'),
                'required_fields_note' => esc_html__('indicates required fields', 'starfish'),
            ),
            'feedback'               => array(
                'feedback_text' => esc_html__('Please provide feedback.', 'starfish'),
                'full_name'     => esc_html__('Please enter your name.', 'starfish'),
                'email_address' => esc_html__('Please enter your email.', 'starfish'),
                'phone_number'  => esc_html__('Please enter your phone.', 'starfish'),
            )
        );
    }

    /**
     * Get all the funnel icon configurations
     *
     * @return array
     */
    public static function srm_get_icons()
    {
        return array(
            'GOOGLE'        => new SRM_FUNNEL_ICONS(esc_html('Google'), 'icon_google fab fa-google'),
            'FACEBOOK'      => new SRM_FUNNEL_ICONS(esc_html('Facebook'), 'icon_facebook fab fa-facebook-f'),
            'YELP'          => new SRM_FUNNEL_ICONS(esc_html('Yelp'), 'icon_yelp fab fa-yelp'),
            'AMAZON'        => new SRM_FUNNEL_ICONS(esc_html('Amazon'), 'icon_amazon fab fa-amazon'),
            'AUDIBLE'       => new SRM_FUNNEL_ICONS(esc_html('Audible'), 'icon_audible fab fa-audible'),
            'ITUNES'        => new SRM_FUNNEL_ICONS(esc_html('iTunes'), 'icon_itunes fab fa-itunes-note'),
            'APPLEAPPSTORE' => new SRM_FUNNEL_ICONS(esc_html('AppleAppStore'), 'icon_apple fab fa-apple'),
            'GOOGLEPLAY'    => new SRM_FUNNEL_ICONS(esc_html('GooglePlay'), 'icon_google fab fa-google-play'),
            'FOURSQUARE'    => new SRM_FUNNEL_ICONS(esc_html('Foursquare'), 'icon_foursquare fab fa-foursquare'),
            'WORDPRESS'     => new SRM_FUNNEL_ICONS(esc_html('WordPress'), 'icon_wordpress fab fa-wordpress'),
            'ETSY'          => new SRM_FUNNEL_ICONS(esc_html('Etsy'), 'icon_etsy fab fa-etsy'),
            'YOUTUBE'       => new SRM_FUNNEL_ICONS(esc_html('YouTube'), 'icon_youtube fab fa-youtube'),
        );
    }

    /**
     * Get the URL of an attachment ID
     *
     * @param $icon_id
     *
     * @return mixed
     */
    public static function get_icon_url($icon_id)
    {
        return wp_get_attachment_url(intval($icon_id));
    }

    /**
     * Get invalid funnel notice.
     *
     * @param integer $funnel_id The funnel ID.
     *
     * @return string $notice
     */
    public static function srm_invalid_funnel_notice($funnel_id)
    {
        $title   = __('Starfish Notice', 'starfish');
        $message = sprintf(__('This funnel (%1$s) is not valid or published yet!', 'starfish'), $funnel_id);

        return sprintf('<div class="srm-notice-message"><i class="fas fa-exclamation-circle fa-2x"> %s</i><br/>%s</div>', $title, $message);
    }

    /**
     * The lastest Funnel ID.
     *
     * @return string $lastFunnelId
     */
    public static function srm_get_last_funnel_id()
    {
        global $wpdb;
        $lastrowId = $wpdb->get_col("SELECT ID FROM $wpdb->posts where post_type='funnel' and post_status='publish' ORDER BY post_date DESC ");
        if (isset($lastrowId[0])) {
            $lastFunnelId = $lastrowId[0];
        } else {
            $lastFunnelId = '';
        }

        return $lastFunnelId;
    }

    /**
     * Enqueue the Funnel associated scripts.
     *
     * @param $post_id
     *
     * @return null
     */
    public static function srm_enqueue_public_funnel_scripts($funnel_id)
    {
        wp_enqueue_style('srm-funnel', SRM_PLUGIN_URL . '/css/starfish-funnel.css', null, SRM_VERSION, false);
        wp_enqueue_style('srm-stepper', 'https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css', null, '1.7.0', false);
        wp_enqueue_script('srm-stepper', 'https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js', null, '1.7.0', false);
        wp_enqueue_script('srm-funnel-feedback', SRM_PLUGIN_URL . '/js/starfish-funnel-feedback.js', null, SRM_VERSION, false);
        wp_enqueue_script('srm-funnel-' . $funnel_id, SRM_PLUGIN_URL . '/js/starfish-funnel.js', null, SRM_VERSION, false);
    }

    /**
     * Localize the funnel JavaScript with template variables
     *
     * @param string $funnel_id
     *
     * @return array $template_variables
     */
    public static function srm_localize_funnel_scripts($funnel_id)
    {
        // Script Localization for Custom Post Type page:
        $tracking_id     = (isset($_GET['id'])) ? esc_html($_GET['id']) : null;
        $feedback        = (isset($_GET['feedback'])) ? esc_html($_GET['feedback']) : null;
        $local_variables = self::srm_get_funnel_variables($funnel_id, $tracking_id, $feedback);
        wp_localize_script('srm-funnel-' . $funnel_id, 'funnel_vars', $local_variables);

        return $local_variables;
    }
}
