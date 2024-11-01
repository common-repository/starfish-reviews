<?php

use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Funnels as SRM_FUNNELS;
use Starfish\Premium\Collections as SRM_COLLECTIONS;
use Starfish\Testimonials as SRM_TESTIMONIALS;
use Starfish\Settings as SRM_SETTINGS;
use Starfish\Utils as SRM_UTILS;

/**
 * Main scripts
 * @throws Freemius_Exception
 */
function srm_main_scripts()
{
    global $post, $pagenow;
    // Global site scripts.
    wp_enqueue_style('srm-main', SRM_PLUGIN_URL . '/css/starfish-main.css', array( 'srm-fontawesome', 'jquery-ui', 'srm-bootstrap' ), SRM_VERSION, 'all');
    wp_enqueue_script('srm-main', SRM_PLUGIN_URL . '/js/starfish-main.js', array( 'jquery', 'jquery-ui', 'srm-bootstrap' ), SRM_VERSION, true);

    $funnel_id     = SRM_UTILS::is_shortcode_present($post, 'funnel');
    $collection_id = SRM_UTILS::is_shortcode_present($post, 'collection');
    $testimonial   = SRM_UTILS::is_shortcode_present($post, 'testimonial');
    if(SRM_UTILS::is_starfish_page($post, $pagenow, $_GET) || (!empty($post) && ($funnel_id !== false || $collection_id !== false || $testimonial !== false))) {
        // Check if shortcodes are being used and enqueue styles/scripts accordingly.
        if ($funnel_id !== false) {
            SRM_FUNNELS::srm_enqueue_public_funnel_scripts($funnel_id);
            SRM_FUNNELS::srm_localize_funnel_scripts($funnel_id);
        }
        if ($testimonial !== false) {
            SRM_TESTIMONIALS::srm_enqueue_testimonial_scripts();
            SRM_TESTIMONIALS::srm_localize_testimonial_scripts();
        }
        if ($collection_id !== false && SRM_FREEMIUS::starfish_fs()->is__premium_only()) {
            SRM_COLLECTIONS::srm_enqueue_collection_scripts($collection_id);
        }
    }
}

add_action('wp_enqueue_scripts', 'srm_main_scripts');
add_action('admin_enqueue_scripts', 'srm_main_scripts');

/**
 * Admin scripts
 */
function srm_admin_scripts()
{
    global $pagenow, $post;
    $current_user = wp_get_current_user();

    $fa_data = array(
        'has_license' => SRM_FREEMIUS::starfish_fs()->has_active_valid_license(),
    );

    $funnel_default_settings = SRM_SETTINGS::srm_get_options('funnel_general');
    foreach ($funnel_default_settings as $key => $option) {
        $funnel_default_settings[ $key ]['current'] = get_option($key, $option['default']);
    }

    // Localized Variables.
    $admin_settings_variables = array(
        'defaults'         => $funnel_default_settings,
        'ajax_url'         => admin_url('admin-ajax.php'),
        'collection_nonce' => wp_create_nonce('srm_collection'),
        'settings_nonce'   => wp_create_nonce('srm_settings'),
        'help_data'        => array(
            'user_display_name' => esc_html($current_user->display_name),
            'user_email'        => esc_html($current_user->user_email),
        ),
    );

    $admin_testimonial_variables = array(
        'ajax_url'          => admin_url('admin-ajax.php'),
        'testimonial_nonce' => wp_create_nonce('srm_testimonial'),
    );

    $admin_funnel_variables = array(
        'icons' => SRM_FUNNELS::srm_get_icons(),
    );

    // Global WordPress: scripts and styles.
    wp_enqueue_style('srm-highlight', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/agate.min.css', false, '11.6.0', 'all');
    wp_enqueue_style('srm-admin-main', SRM_PLUGIN_URL . '/css/starfish-admin-main.css', array( 'srm-main' ), SRM_VERSION, 'all');
    wp_enqueue_script('srm-highlight', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js', false, '11.6.0', false);
    wp_enqueue_script('srm-admin-main', SRM_PLUGIN_URL . '/js/starfish-admin-main.js', array( 'srm-main' ), SRM_VERSION, false);

    // Global Starfish: scripts and styles.
    if (SRM_UTILS::is_starfish_page($post, $pagenow, $_GET)) {
        wp_enqueue_style('srm-settings', SRM_PLUGIN_URL . '/css/starfish-settings.css', array( 'srm-main' ), SRM_VERSION, 'all');
        wp_enqueue_style('srm-toggle', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css', false, '2.2.2', 'all');
        wp_enqueue_style('srm-confirm', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css', false, '3.3.4', 'all');
        wp_enqueue_script('srm-clipboard', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js', false, '2.0.11', false);  // Cannot be in footer.
        wp_enqueue_script('srm-main', SRM_PLUGIN_URL . '/js/starfish-main.js', array( 'srm-main' ), SRM_VERSION, true);
        wp_enqueue_script('srm-freemius-account', SRM_PLUGIN_URL . '/js/starfish-admin-freemius-account.js', array( 'srm-main' ), SRM_VERSION, false);
        wp_enqueue_script('srm-confirm', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js', false, '3.3.4', false);
        wp_enqueue_script('srm-toggle', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js', false, '2.2.2', false);
        // Script Localization
        wp_localize_script('srm-freemius-account', 'fa_data', $fa_data);
    }

    // Funnel: Admin page.
    if (SRM_UTILS::is_starfish_funnel($post, $pagenow)) {
        $post_id                               = $post->ID;
        $admin_settings_variables['funnel_id'] = $post_id;
        wp_enqueue_style('srm-funnels-admin', SRM_PLUGIN_URL . '/css/starfish-funnel-admin.css', array( 'srm-main' ), SRM_VERSION, 'all');
        wp_enqueue_script('srm-funnel-admin', SRM_PLUGIN_URL . '/js/starfish-admin-funnel.js', array( 'srm-main' ), SRM_VERSION, false);
        wp_enqueue_script('srm-funnel-admin-multi-destinations', SRM_PLUGIN_URL . '/js/starfish-admin-funnel-multidestinations.js', array( 'srm-main' ), SRM_VERSION, false);
        // Script Localization: Admin Funnel.
        wp_localize_script('srm-funnel-admin-multi-destinations', 'funnel_vars', $admin_funnel_variables);
        wp_localize_script('srm-funnel-admin', 'settings', $admin_settings_variables);
    }

    // Feedback: Admin page.
    if (SRM_UTILS::is_starfish_feedback($post, $pagenow)) {
        $admin_feedback_variables['funnel_id']      = (isset($_GET['funnel_id'])) ? esc_html($_GET['funnel_id']) : null;
        $admin_feedback_variables['month']          = (isset($_GET['m'])) ? $_GET['m'] : null;
        $admin_feedback_variables['feedback_nonce'] = wp_create_nonce('srm-export-feedback', 'export_feedback_nonce');
        wp_enqueue_style('srm-feedback-admin', SRM_PLUGIN_URL . '/css/starfish-admin-feedback.css', array( 'srm-main' ), SRM_VERSION, 'all');
        wp_enqueue_script('srm-feedback-admin', SRM_PLUGIN_URL . '/js/starfish-admin-feedback.js', array( 'srm-main' ), SRM_VERSION, false);
        // Script Localization: Admin Feedback.
        wp_localize_script('srm-feedback-admin', 'feedback_vars', $admin_feedback_variables);
    }
    // Testimonial: Admin page.
    if (SRM_UTILS::is_starfish_testimonial($post, $pagenow)) {
        wp_enqueue_style('srm-testimonial-admin', SRM_PLUGIN_URL . '/css/starfish-admin-testimonial.css', array( 'srm-main' ), SRM_VERSION, 'all');
        wp_enqueue_script('srm-testimonials-admin', SRM_PLUGIN_URL . '/js/starfish-admin-testimonials.js', array( 'srm-main' ), SRM_VERSION, false);
        SRM_TESTIMONIALS::srm_enqueue_testimonial_scripts();
        // Script Localization: Admin Reviews Profile.
        wp_localize_script('srm-testimonials-admin', 'testimonial_vars', $admin_testimonial_variables);
    }

    // Starfish Settings.
    if (SRM_UTILS::is_starfish_settings($post, $pagenow, $_GET)) {
        wp_enqueue_script('srm-admin-settings', SRM_PLUGIN_URL . '/js/starfish-admin-settings.js', array( 'srm-main' ), SRM_VERSION, false);
        // Script Localization: Admin Settings.
        wp_localize_script('srm-admin-settings', 'settings_vars', $admin_settings_variables);
    }
}

add_action('admin_enqueue_scripts', 'srm_admin_scripts');
