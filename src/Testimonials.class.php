<?php

namespace Starfish;

use PHPMailer\PHPMailer\Exception;
use Starfish\Logging as SRM_LOGGING;
use Starfish\Settings as SRM_SETTINGS;
use WP_Query;

/**
 * Collection class for dealing with collections
 *
 * @category   Testimonials
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     Matt Galloway <matt@starfish.reviews>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0.0
 * @since      Release: 3.1.0
 */
class Testimonials
{
    /**
     * Insert new testimonial submission.
     *
     * @param string $data Form POST Payload.
     *
     * @return bool
     */
    public static function srm_insert_testimonial($data)
    {

        $srm_error_cya  = '01';
        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Received new Testimonial Submission',
                'message' => print_r($data, true),
                'code'    => 'SRM_C_ITS_X_' . $srm_error_cya,
            )
        );

        $category = sanitize_text_field($data['srm-testimonial-category'] ?? null);
        $referrer = sanitize_text_field($data['srm-testimonial-referrer'] ?? null);
        $rating   = sanitize_text_field($data['srm-testimonial-rating'] ?? null);
        $name     = sanitize_text_field($data['srm-testimonial-name'] ?? null);
        $email    = sanitize_email($data['srm-testimonial-email'] ?? null);
        $phone    = sanitize_text_field($data['srm-testimonial-phone'] ?? null);
        $message  = sanitize_text_field($data['srm-testimonial-message'] ?? null);

        $current_local_time = date('j M Y g:i a', current_time('timestamp', 0));

        // add new category if current one doesn't exist.
        if (!empty($category)) {
            $args = ['taxonomy' => 'category', 'orderby' => 'name', 'order' => 'ASC',];
            $current_categories = get_categories($args);
            $category_exists = false;
            foreach ($current_categories as $c) {
                if ($c->name == $category) {
                    $category_id = $c->term_id;
                    $category_exists = true;
                    break;
                }
            }
            if (!$category_exists) {
                $category_id = wp_create_category($category);
            }
        }

        // add new testimonial.
        $args = array(
            'post_type'   => 'starfish_testimonial',
            'post_title'  => $current_local_time,
            'post_status' => 'pending',
        );

        $testimonial_id = wp_insert_post($args);

        // Update new testimonial's meta data.
        if ($testimonial_id !== 0 || !is_wp_error($testimonial_id)) {

            $srm_error_cya  = '02';
            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'Successfully created new Testimonial Post',
                    'message' => print_r($testimonial_id, true),
                    'code'    => 'SRM_C_ITS_X_' . $srm_error_cya,
                )
            );

            if (!empty($category)) {
                wp_set_post_categories($testimonial_id, $category_id ?? null);
            }
            add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_REFERRER, $referrer);
            add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_RATING, $rating);
            add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_NAME, $name);
            add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_EMAIL, $email);
            add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_PHONE, $phone);
            add_post_meta($testimonial_id, SRM_META_TESTIMONIAL_MESSAGE, $message);

            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'Successfully updated Testimonial\'s Meta Data',
                    'message' => print_r($testimonial_id, true),
                    'code'    => 'SRM_C_ITS_X_' . $srm_error_cya,
                )
            );

            // Check if Email Notifications are enabled, if so, send notification of new testimonial received
            $srm_error_cya  		 = '03';
            $is_notification_enabled = filter_var(get_option(SRM_OPTION_TESTIMONIAL_ENABLE_EMAIL_NOTIFICATION, false), FILTER_VALIDATE_BOOLEAN);
            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'Are Testimonial Notifications Turned on?',
                    'message' => $is_notification_enabled,
                    'code'    => 'SRM_C_ITS_X_' . $srm_error_cya,
                )
            );
            if ($is_notification_enabled) {
                $notification = self::srm_send_testimonial_notification($testimonial_id);
                if ($notification) {
                    SRM_LOGGING::addEntry(
                        array(
                            'level'   => SRM_LOGGING::SRM_INFO,
                            'action'  => 'Sent Testimonial Notification',
                            'message' => print_r($testimonial_id, true),
                            'code'    => 'SRM_C_ITS_X_' . $srm_error_cya,
                        )
                    );
                } else {
                    SRM_LOGGING::addEntry(
                        array(
                            'level'   => SRM_LOGGING::SRM_WARN,
                            'action'  => 'Failed to send Testimonial Notification',
                            'message' => 'testimonial_id=' . $testimonial_id . ', exception=' . print_r($notification, true),
                            'code'    => 'SRM_C_ITS_X_' . $srm_error_cya,
                        )
                    );
                }
            }
            return true;
        } else {
            $srm_error_cya  = '04';
            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_ERROR,
                    'action'  => 'Failed to create new Testimonial Post',
                    'message' => print_r($testimonial_id, true),
                    'code'    => 'SRM_C_ITS_X_' . $srm_error_cya,
                )
            );
            return false;
        }

    }

    /**
     * Get a testimonial by ID
     *
     * @param $testimonial_id string Testimonial ID
     *
     * @return object $testimonial The Testimonial
     */
    public static function srm_get_testimonial($testimonial_id)
    {
        $args = array(
            'numberposts'      => 1,
            'category'         => 0,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'include'          => array( $testimonial_id ),
            'exclude'          => array(),
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'starfish_testimonial',
            'suppress_filters' => true,
        );

        $posts = get_posts($args);

        return $posts[0];
    }

    /**
     * @param string  $testimonial_id The Testimonial ID
     *
     * @param boolean $hidden whether to hide the Testimonial or not
     *
     * @return boolean True or false whether the update was successful
     */
    public static function srm_update_testimonial_hide($testimonial_id, $hidden)
    {
        return update_post_meta($testimonial_id, '_srm_testimonial_hidden', $hidden);
    }

    /**
     * Get invalid Testimonial notice.
     *
     * @param integer $testimonial_id The Testimonial ID.
     *
     * @return string $notice
     */
    public static function srm_invalid_testimonial_notice($testimonial_id)
    {
        $notice  = '<div class="srm-notice-message">';
        $notice .= '<i class="fas fa-exclamation-circle fa-2x"> ' . __('Starfish Notice', 'starfish') . '</i><br/>';
        $notice .= sprintf(__('This Testimonial (%1$s) is not valid or published yet!', 'starfish'), $testimonial_id);
        $notice .= '</div>';

        return $notice;
    }

    /**
     * Enqueue the Testimonial associated scripts.
     *
     * @return null
     */
    public static function srm_enqueue_testimonial_scripts()
    {
        $kartik_version = '4.1.2';
        wp_enqueue_style('srm-testimonial', SRM_PLUGIN_URL . '/css/starfish-testimonial.css', false, SRM_VERSION, 'all');
        wp_enqueue_style('srm-kartik-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/'.$kartik_version.'/css/star-rating.min.css', false, $kartik_version, 'all');
        wp_enqueue_style('srm-kartik-theme', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/'.$kartik_version.'/themes/krajee-svg/theme.min.css', false, $kartik_version, 'all');
        wp_enqueue_script('srm-kartik-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/'.$kartik_version.'/js/star-rating.min.js', false, $kartik_version, false);
        wp_enqueue_script('srm-kartik-theme', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/'.$kartik_version.'/themes/krajee-svg/theme.min.js', false, $kartik_version, false);
        wp_enqueue_script('srm-testimonial', SRM_PLUGIN_URL . '/js/starfish-testimonial.js', false, SRM_VERSION, false);
    }

    /**
     * Return the array of pages or posts where the testimonial shortcode is being used.
     *
     * @param $testimonial_id The Testimonial ID.
     *
     * @return array|object|null
     */
    public static function srm_get_testimonial_usage($testimonial_id)
    {
        global $wpdb;
        $query = 'SELECT ID, post_title, post_type FROM ' . $wpdb->posts . " WHERE post_content LIKE '%[starfish%testimonial=%{$testimonial_id}%' AND post_status = 'publish'";

        return $wpdb->get_results($query);
    }

    /**
     * Get total collection count
     *
     * @return mixed
     */
    public static function srm_get_total_testimonials($status = 'publish')
    {
        $args           = array(
            'post_type'      => array( 'starfish_testimonial' ),
            'post_status'    => $status,
            'posts_per_page' => -1,
        );
        $starfish_query = new WP_Query($args);

        return $starfish_query->post_count;

    }

    /**
     * Get total collection count
     *
     * @return mixed
     */
    public static function srm_get_testimonials($status = 'publish', $post_ids = 'all', $category = null, $limit = -1)
    {
        $args           = array(
            'post_type'   => array( 'starfish_testimonial' ),
            'post_status' => $status,
            'order' 	  => 'DESC',
            'orderby' 	  => 'date',
        );

        if('all' !== $post_ids && !empty($post_ids)) {
            $args['post__in'] = explode(',', $post_ids);
        }

        if('all' === $post_ids || empty($post_ids)) {
            $args['numberposts'] = $limit;
        }

        if(!empty($category)) {
            $args['cat'] = get_cat_ID($category);
        }

        return get_posts($args);
    }

    /**
     * Get Testimonial form variables based on shortcode attributes
     *
     * @return array Form Variables.
     */
    public static function srm_get_testimonial_form_variables($atts = null)
    {
        global $wp;
        return array(
            'ajax_url'       => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('srm-testimonial-submit'),
            'shortcode'      => true,
            'category'       => $atts['category'] ?? null,
            'hide_branding'  => get_option(SRM_OPTION_HIDE_BRANDING, false),
            'affiliate_url'  => get_option(SRM_OPTION_AFFILIATE_URL, SRM_PLANS_URL),
            'authentication' => array(
                'is_logged_in' => is_user_logged_in(),
                'required'     => get_option(SRM_OPTION_TESTIMONIAL_REQUIRE_LOGIN, false),
                'message'      => get_option(SRM_OPTION_TESTIMONIAL_NOT_AUTHENTICATED_MESSAGE, esc_html__('Please login.', 'starfish')),
            ),
            'instructions'   => get_option(SRM_OPTION_TESTIMONIAL_FORM_INSTRUCTIONS, esc_html__('Please rate us.', 'starfish')),
            'components'     => (isset($atts['components'])) ? explode(',', $atts['components']) : null,
            'required'       => (isset($atts['required'])) ? explode(',', $atts['required']) : null,
            'referrer'       => home_url($wp->request),
            'errors'         => array(
                'rating'  => get_option(SRM_OPTION_TESTIMONIAL_ERROR_RATING, esc_html__('Rating is a required field.', 'starfish')),
                'name'    => get_option(SRM_OPTION_TESTIMONIAL_ERROR_NAME, esc_html__('Name is a required field.', 'starfish')),
                'email'   => get_option(SRM_OPTION_TESTIMONIAL_ERROR_EMAIL, esc_html__('Email is a required field.', 'starfish')),
                'phone'   => get_option(SRM_OPTION_TESTIMONIAL_ERROR_PHONE, esc_html__('Phone is a required field.', 'starfish')),
                'message' => get_option(SRM_OPTION_TESTIMONIAL_ERROR_MESSAGE, esc_html__('Message is a required field.', 'starfish')),
            ),
            'labels'         => array(
                'category' => get_option(SRM_OPTION_TESTIMONIAL_LABELS_CATEGORY, esc_html__('Subject', 'starfish')),
                'name'     => get_option(SRM_OPTION_TESTIMONIAL_LABELS_NAME, esc_html__('Name', 'starfish')),
                'email'    => get_option(SRM_OPTION_TESTIMONIAL_LABELS_EMAIL, esc_html__('Email', 'starfish')),
                'phone'    => get_option(SRM_OPTION_TESTIMONIAL_LABELS_PHONE, esc_html__('Phone', 'starfish')),
                'rating'   => get_option(SRM_OPTION_TESTIMONIAL_LABELS_RATING, esc_html__('Rating', 'starfish')),
                'message'  => get_option(SRM_OPTION_TESTIMONIAL_LABELS_MESSAGE, esc_html__('Message', 'starfish')),
            ),
        );
    }

    /**
     * Get Testimonial display variables based on shortcode attributes
     *
     * @return array Form Variables.
     */
    public static function srm_get_testimonial_display_variables($atts = null)
    {

        // Get array of testimonial posts for display
        $category   = $atts['category'] ?? null; // Used to filter the testimonials returned if "posts" = "all"
        $posts      = $atts['posts'] ?? null;  // Comma separated list of post ID's, "all" (default)
        $limit 	    = $atts['limit'] ?? null;  // Limit how many posts are returned (only applies when "all" is used within "posts"
        $per_page   = $atts['per_page'] ?? null; // number of posts per slide, used to determine how many slides there will be
        $components = (isset($atts['components'])) ? explode(',', $atts['components']) : null;

        $all_testimonials = self::srm_get_testimonials('publish', $posts, $category, $limit);
        $testimonials 	  = [];
        foreach ($all_testimonials as $t) {
            $testimonials[] = [
                'id' 			=> $t->ID,
                'uuid' 			=> $t->ID,
                'name' 			=> empty(get_post_meta($t->ID, SRM_META_TESTIMONIAL_NAME, true)) ? 'Anonymous' : get_post_meta($t->ID, SRM_META_TESTIMONIAL_NAME, true) ,
                'rating' 		=> get_post_meta($t->ID, SRM_META_TESTIMONIAL_RATING, true),
                'review_text' 	=> get_post_meta($t->ID, SRM_META_TESTIMONIAL_MESSAGE, true),
                'date' 			=> $t->post_date,
                'url' 			=> get_post_meta($t->ID, SRM_META_TESTIMONIAL_REFERRER, true),
                'avatar_url' 	=> empty(get_post_meta($t->ID, SRM_META_TESTIMONIAL_AVATAR, true)) ? SRM_PLUGIN_URL . '/assets/default_profile_image.png' : get_post_meta($t->ID, SRM_META_TESTIMONIAL_AVATAR, true)
            ];
        }

        $total_testimonials = count($testimonials);
        $page_count 		= 1; // Start with one page
        $pages 	 	 		= null;
        if ($per_page > 0 && !empty($per_page)) {
            $page_count = ceil($total_testimonials / $per_page);
        }
        if (!empty($page_count)) {
            for ($s = 1; $s <= $page_count; $s++) {
                $pages[] = array_slice($testimonials, 0, $per_page);
            }
        }

        return array(
            'ajax_url'       => admin_url('admin-ajax.php'),
            'pages'	 		 => $pages,  // Testimonials broken down by pages
            'slider'		 => $page_count > 1 ? filter_var('true', FILTER_VALIDATE_BOOLEAN) : filter_var('false', FILTER_VALIDATE_BOOLEAN),
            'hide_branding'  => get_option(SRM_OPTION_HIDE_BRANDING, false),
            'affiliate_url'  => get_option(SRM_OPTION_AFFILIATE_URL, SRM_PLANS_URL),
            'shortcode_id'	 => rand(0, 20),
            'components'	 => $components,
        );
    }

    /**
     * Localize the testimonial JavaScript with template variables
     *
     * @param string|null $atts Testimonial Shortcode Attributes.
     *
     * @return array $template_variables
     */
    public static function srm_localize_testimonial_scripts($atts = null)
    {
        $local_variables = self::srm_get_testimonial_form_variables($atts);
        wp_localize_script('srm-testimonial', 'testimonial_vars', $local_variables);
        return $local_variables;
    }

    /**
     * Send notification of new testimonial being recieved
     *
     * @param string $testimonial_id Testimonial ID
     *
     * @return boolean
     */
    public static function srm_send_testimonial_notification($testimonial_id)
    {

        $srm_error_cya = '01';
        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Preparing to send Testimonial Notification',
                'message' => $testimonial_id,
                'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
            )
        );

        $admin_email 		  = get_option('admin_email');
        $site_name 	 		  = get_option('blogname');
        $site_url	 		  = get_site_url();
        $testimonial_referrer = get_post_meta($testimonial_id, SRM_META_TESTIMONIAL_REFERRER, true);
        $testimonial_rating   = get_post_meta($testimonial_id, SRM_META_TESTIMONIAL_RATING, true);
        $testimonial_name 	  = get_post_meta($testimonial_id, SRM_META_TESTIMONIAL_NAME, true);
        $testimonial_email 	  = get_post_meta($testimonial_id, SRM_META_TESTIMONIAL_EMAIL, true);
        $testimonial_phone 	  = get_post_meta($testimonial_id, SRM_META_TESTIMONIAL_PHONE, true);
        $testimonial_message  = get_post_meta($testimonial_id, SRM_META_TESTIMONIAL_MESSAGE, true);
        $testimonial_all	  = array(
            'referrer' 	=> $testimonial_referrer,
            'rating' 	=> $testimonial_rating,
            'name' 		=> $testimonial_name,
            'email' 	=> $testimonial_email,
            'phone' 	=> $testimonial_phone,
            'message' 	=> $testimonial_message
        );

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Captured Testimonial Data ('.$testimonial_id.')',
                'message' => print_r($testimonial_all, true),
                'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
            )
        );

        $init_email_subject = get_option(SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_SUBJECT, SRM_SETTINGS::srm_get_options('testimonial_email')[ SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_SUBJECT ]['default']);
        $search_subject 	= [];
        $replace_subject 	= [];
        $search_subject[] 	= '{site-name}';
        $replace_subject[] 	= $site_name;
        $search_subject[] 	= '{testimonial-rating}';
        $replace_subject[] 	= $testimonial_rating;
        $search_subject[] 	= '{testimonial-author}';
        $replace_subject[] 	= $testimonial_name;
        $search_subject[] 	= '{testimonial-phone}';
        $replace_subject[] 	= $testimonial_phone;
        $search_subject[] 	= '{testimonial-email}';
        $replace_subject[] 	= $testimonial_email;
        $set_email_subject 	= str_replace($search_subject, $replace_subject, $init_email_subject);
        $email_subject 		= esc_html($set_email_subject);

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Set Notification Subject ('.$testimonial_id.')',
                'message' => print_r($email_subject, true),
                'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
            )
        );

        // process email message
        $content 			= get_option(SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_CONTENT, SRM_SETTINGS::srm_get_options('testimonial_email')[ SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_CONTENT ]['default']);
        $search_content 	= [];
        $replace_content 	= [];
        $search_content[] 	= '{admin-email}';
        $replace_content[]	= $admin_email;
        $search_content[]	= '{site-name}';
        $replace_content[] 	= $site_name;
        $search_content[] 	= '{site-url}';
        $replace_content[] 	= $site_url;
        $search_content[] 	= '{testimonial-all}';
        $replace_content[] 	= $testimonial_all;
        $search_content[] 	= '{testimonial-referrer}';
        $replace_content[] 	= $testimonial_referrer;
        $search_content[] 	= '{testimonial-rating}';
        $replace_content[] 	= $testimonial_rating;
        $search_content[] 	= '{testimonial-author}';
        $replace_content[] 	= $testimonial_name;
        $search_content[] 	= '{testimonial-phone}';
        $replace_content[] 	= $testimonial_phone;
        $search_content[] 	= '{testimonial-email}';
        $replace_content[] 	= $testimonial_email;
        $search_content[] 	= '{testimonial-message}';
        $replace_content[] 	= $testimonial_message;
        $message 			= str_replace($search_content, $replace_content, $content);
        $email_message 		= stripslashes($message);

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Set Notification Message ('.$testimonial_id.')',
                'message' => print_r($email_message, true),
                'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
            )
        );

        // process email addresses
        $init_email_addresses 	= esc_html(get_option(SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_RECIPIENT, SRM_SETTINGS::srm_get_options('testimonial_email')[ SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_RECIPIENT ]['default']));
        $search_addresses  	 	= [];
        $replace_addresses 	  	= [];
        $search_addresses[]   	= '{admin-email}';
        $replace_addresses[]  	= $admin_email;
        $set_email_addresses  	= str_replace($search_addresses, $replace_addresses, $init_email_addresses);
        $email_addresses 	  	= explode(',', $set_email_addresses);

        $init_from_name 		= esc_html(get_option(SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_NAME, SRM_SETTINGS::srm_get_options('testimonial_email')[ SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_NAME ]['default']));
        $search_from_name 	  	= [];
        $replace_from_name 	  	= [];
        $search_from_name[]   	= '{site-name}';
        $replace_from_name[]  	= $site_name;
        $email_from_name 	  	= esc_html(str_replace($search_from_name, $replace_from_name, $init_from_name));

        $init_from_email 		= get_option(SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_EMAIL, SRM_SETTINGS::srm_get_options('testimonial_email')[ SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_EMAIL ]['default']);
        $search_from_email  	= [];
        $replace_from_email 	= [];
        $search_from_email[] 	= '{admin-email}';
        $replace_from_email[] 	= $admin_email;
        $email_from_email 		= str_replace($search_from_email, $replace_from_email, $init_from_email);

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Set Notification From ('.$testimonial_id.')',
                'message' => print_r($email_from_name . ' <' . $email_from_email . '>', true),
                'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
            )
        );

        // set reply-to email address
        $reply_to_email = '';
        if (isset($testimonial_email) && is_email($testimonial_email)) {
            $reply_to_email = $testimonial_email;
        } elseif (is_email($email_from_email)) {
            $reply_to_email = $email_from_email;
        }

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Set Notification Reply-To ('.$testimonial_id.')',
                'message' => print_r($reply_to_email, true),
                'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
            )
        );

        $headers[] = 'MIME-Version: 1' . "\r\n";
        $headers[] = 'Content-Type: text/html; charset=UTF-8' . "\r\n";

        //get the full domain
        $urlparts = wp_parse_url(site_url());
        $domain = $urlparts ['host'];

        //get the TLD and domain
        $domainparts = explode(".", $domain);
        $domain = $domainparts[count($domainparts) - 2] . "." . $domainparts[count($domainparts) - 1];

        if ((!empty($email_from_name)) && (!empty($email_from_email))) {
            $headers[] = 'From: ' . wp_specialchars_decode(esc_html($email_from_name), ENT_QUOTES) . ' <' . apply_filters('starfish_notification_email_header_from_email', $email_from_email) . ">\r\n";
        } else {
            $headers[] = 'From: no-reply@' . $domain . ' <no-reply@' . $domain . ">\r\n";
        }

        if (!empty($reply_to_email)) {
            $headers[] = 'Reply-To: <' . $reply_to_email . '>';
        }

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Set Notification Headers ('.$testimonial_id.')',
                'message' => print_r($headers, true),
                'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
            )
        );

        if (!empty(array_filter($email_addresses))) {
            foreach ($email_addresses as $email_address) {
                $email_address = trim($email_address);
                try {
                    add_filter('wp_mail_content_type', 'srm_set_html_mail_content_type');
                    wp_mail($email_address, $email_subject, html_entity_decode($email_message), $headers);
                    remove_filter('wp_mail_content_type', 'srm_set_html_mail_content_type');
                    $srm_error_cya = '02';
                    SRM_LOGGING::addEntry(
                        array(
                            'level'   => SRM_LOGGING::SRM_INFO,
                            'action'  => 'Sent Testimonial Notification ('.$testimonial_id.')',
                            'message' => print_r($email_address, true),
                            'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
                        )
                    );
                    return true;
                } catch (Exception $e) {
                    $srm_error_cya = '02';
                    SRM_LOGGING::addEntry(
                        array(
                            'level'   => SRM_LOGGING::SRM_ERROR,
                            'action'  => 'Failed to send Testimonial Notification ('.$testimonial_id.')',
                            'message' => print_r($e, true),
                            'code'    => 'SRM_C_STN_X_' . $srm_error_cya,
                        )
                    );
                    return $e;
                }
            }
        }
    }

}
