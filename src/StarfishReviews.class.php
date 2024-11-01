<?php

namespace Starfish;

use  Starfish\Freemius as SRM_FREEMIUS ;
use  Starfish\Cron as SRM_CRON ;
use  Starfish\Logging as SRM_LOGGING ;
use function  define ;
use function  str_replace ;
use function  wp_get_upload_dir ;
use const  SRM_PLUGIN_PATH ;
class StarfishReviews
{
    /**
     * Initiate the Starfish Activation & Deactivation Logic.
     */
    public static function init()
    {
        self::plugin_constants();
        // Load Core Starfish classes
        include SRM_PLUGIN_PATH . 'src/Freemius.class.php';
        include SRM_PLUGIN_PATH . 'src/Cron.class.php';
        include SRM_PLUGIN_PATH . 'src/Settings.class.php';
        // Autoload all other Starfish classes
        spl_autoload_register( function ( $class ) {
            if ( strpos( $class, SRM_NAMESPACE ) !== false ) {
                include SRM_PLUGIN_PATH . str_replace( SRM_NAMESPACE, 'src/', str_replace( '\\', '/', $class ) ) . '.class.php';
            }
        } );
        add_action( 'init', array( __CLASS__, 'load_plugin_languages' ) );
        SRM_CRON::init();
        require_once SRM_PLUGIN_PATH . 'init/starfish-activation.php';
        require_once SRM_PLUGIN_PATH . 'init/starfish-deactivation.php';
        register_activation_hook( SRM_MAIN_FILE, 'srm_plugin_install' );
        register_deactivation_hook( SRM_MAIN_FILE, 'srm_plugin_uninstall' );
        self::srm_actions();
        self::srm_filters();
        self::srm_includes();
        self::srm_shortcodes();
        // Premium Plan
        define( 'SRM_FREEMIUS_PLAN_TITLE', SRM_FREEMIUS::starfish_fs()->get_plan_title() );
        define( 'SRM_PLAN_TITLE', ( empty(SRM_FREEMIUS_PLAN_TITLE) || SRM_FREEMIUS_PLAN_TITLE == 'PLAN_TITLE' ? _x( 'FREE', 'The Free Subscription Plan', 'starfish' ) : SRM_FREEMIUS_PLAN_TITLE ) );
    }
    
    /**
     * Define Plugin Constants
     */
    public static function plugin_constants()
    {
        $uploads = wp_get_upload_dir();
        define( 'SRM_BUSINESS_PLAN', 'business_v2' );
        define( 'SRM_ENTREPRENEUR_PLAN', 'entrepreneur' );
        define( 'SRM_AGENCY_PLAN', 'agency' );
        define( 'SRM_MARKETER_PLAN_LEGACY', 'marketer' );
        define( 'SRM_WEBMASTER_PLAN_LEGACY', 'webmaster' );
        define( 'SRM_BUSINESS_PLAN_LEGACY', 'business' );
        define( 'SRM_PLUGIN_ACTIVE', true );
        define( 'SRM_SITE_URL', get_site_url() );
        define( 'SRM_PLANS_URL', 'https://starfish.reviews/plans-pricing' );
        define( 'SRM_PRICING_ADMIN_URL', admin_url( '/admin.php?page=starfish-settings-pricing&billing_cycle=annual&trial=true' ) );
        define( 'SRM_NAMESPACE', 'Starfish' );
        define( 'SRM_UPLOADS_DIR', $uploads['basedir'] . '/starfish/' );
        define( 'SRM_UPLOADS_URL', $uploads['baseurl'] . '/starfish/' );
        define( 'SRM_LOG_FILE', SRM_PLUGIN_PATH . 'srm_logs.txt' );
        define( 'SRM_GO_PREMIUM_LINK', '<a class="premium-only-link" href="' . SRM_PLANS_URL . '" title="Upgrade Starfish Plan">' . __( 'Go Premium!', 'starfish' ) . '</a>' );
        // Plugin Option Groups!
        define( 'SRM_OPTION_GROUP_GENERAL', 'srm_general_options' );
        define( 'SRM_OPTION_GROUP_GENERAL_MIGRATIONS', 'srm_general_migrations_options' );
        define( 'SRM_OPTION_GROUP_FUNNEL_GENERAL', 'srm_funnels_general_options' );
        define( 'SRM_OPTION_GROUP_FUNNEL_EMAIL', 'srm_funnels_email_options' );
        define( 'SRM_OPTION_GROUP_TOOLBOX', 'srm_toolbox_options' );
        define( 'SRM_OPTION_GROUP_REVIEWS', 'srm_reviews_options' );
        define( 'SRM_OPTION_GROUP_SUPPORT_GENERAL', 'srm_support_options' );
        define( 'SRM_OPTION_GROUP_SUPPORT_CONSOLE', 'srm_support_console_options' );
        define( 'SRM_OPTION_GROUP_SUPPORT_INFO', 'srm_support_info_options' );
        define( 'SRM_OPTION_GROUP_TESTIMONIAL_GENERAL', 'srm_testimonial_general_options' );
        define( 'SRM_OPTION_GROUP_TESTIMONIAL_FORM', 'srm_testimonial_form_options' );
        define( 'SRM_OPTION_GROUP_TESTIMONIAL_EMAIL', 'srm_testimonial_email_options' );
        define( 'SRM_OPTION_GROUP_TESTIMONIAL_HELP', 'srm_testimonial_help_options' );
        // Plugin Options!
        define( 'SRM_OPTION_RICHREVIEWS_MIGRATED', 'srm_richreviews_migrated' );
        define( 'SRM_OPTION_LOGGING', 'srm_logging' );
        define( 'SRM_OPTION_SUPPORT_ALERTS', 'srm_support_alerts' );
        define( 'SRM_OPTION_INSTALL_DATE', 'srm_install_date' );
        define( 'SRM_OPTION_ALL_FUNNELS', 'srm_all_funnels' );
        define( 'SRM_OPTION_FUNNEL_ARCHIVES', 'srm_funnel_archives' );
        define( 'SRM_OPTION_FUNNEL_SLUG', 'srm_feedback_slug' );
        define( 'SRM_OPTION_HIDE_BRANDING', 'srm_branding' );
        define( 'SRM_OPTION_FUNNEL_REPLY_TO_EMAIL', 'srm_replay_to_email' );
        define( 'SRM_OPTION_CLEAN_ON_DEACTIVATE', 'srm_clean_on_deactive' );
        define( 'SRM_OPTION_AFFILIATE_URL', 'srm_affiliate_url' );
        define( 'SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_NAME', 'srm_email_from_name' );
        define( 'SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_EMAIL', 'srm_email_from_email' );
        define( 'SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_SUBJECT', 'srm_email_subject' );
        define( 'SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_CONTENT', 'srm_email_template' );
        define( 'SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT', 'srm_funnel_feedback_email' );
        define( 'SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU', 'srm_funnel_msg_no_thank_you' );
        define( 'SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT', 'srm_funnel_no_review_prompt' );
        define( 'SRM_OPTION_FUNNEL_REVIEW_PROMPT', 'srm_funnel_review_prompt' );
        define( 'SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO', 'srm_funnel_submit_button_no' );
        define( 'SRM_OPTION_FUNNEL_SUBMIT_BUTTON', 'srm_funnel_submit_button' );
        define( 'SRM_OPTION_FUNNEL_YN_QUESTION', 'srm_funnel_yn_question' );
        define( 'SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS', 'srm_funnel_yes_redirect_seconds' );
        define( 'SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT', 'srm_funnel_public_review_text' );
        define( 'SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT', 'srm_funnel_skip_feedback_text' );
        define( 'SRM_OPTION_REVIEW_SCRAPE_CRON', 'srm_review_scrape_cron' );
        define( 'SRM_OPTION_REVIEW_SCRAPE_PULL_TIME', 'srm_review_scrape_pull_time' );
        define( 'SRM_OPTION_TESTIMONIAL_REQUIRE_LOGIN', 'srm_testimonial_require_login' );
        define( 'SRM_OPTION_TESTIMONIAL_NOT_AUTHENTICATED_MESSAGE', 'srm_testimonial_not_authenticated_message' );
        define( 'SRM_OPTION_TESTIMONIAL_ENABLE_EMAIL_NOTIFICATION', 'srm_testimonial_enable_email_notification' );
        define( 'SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_NAME', 'srm_testimonial_email_notification_from_name' );
        define( 'SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_EMAIL', 'srm_testimonial_email_notification_from_email' );
        define( 'SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_RECIPIENT', 'srm_testimonial_email_notification_recipient' );
        define( 'SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_CONTENT', 'srm_testimonial_email_notification_content' );
        define( 'SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_SUBJECT', 'srm_testimonial_email_notification_subject' );
        define( 'SRM_OPTION_TESTIMONIAL_FORM_SUCCESS', 'srm_testimonial_form_success' );
        define( 'SRM_OPTION_TESTIMONIAL_FORM_INSTRUCTIONS', 'srm_testimonial_form_instructions' );
        define( 'SRM_OPTION_TESTIMONIAL_ERROR_RATING', 'srm_testimonial_error_rating' );
        define( 'SRM_OPTION_TESTIMONIAL_ERROR_NAME', 'srm_testimonial_error_name' );
        define( 'SRM_OPTION_TESTIMONIAL_ERROR_EMAIL', 'srm_testimonial_error_email' );
        define( 'SRM_OPTION_TESTIMONIAL_ERROR_PHONE', 'srm_testimonial_error_phone' );
        define( 'SRM_OPTION_TESTIMONIAL_ERROR_MESSAGE', 'srm_testimonial_error_message' );
        define( 'SRM_OPTION_TESTIMONIAL_LABELS_CATEGORY', 'srm_testimonial_labels_category' );
        define( 'SRM_OPTION_TESTIMONIAL_LABELS_NAME', 'srm_testimonial_labels_name' );
        define( 'SRM_OPTION_TESTIMONIAL_LABELS_EMAIL', 'srm_testimonial_labels_email' );
        define( 'SRM_OPTION_TESTIMONIAL_LABELS_PHONE', 'srm_testimonial_labels_phone' );
        define( 'SRM_OPTION_TESTIMONIAL_LABELS_RATING', 'srm_testimonial_labels_rating' );
        define( 'SRM_OPTION_TESTIMONIAL_LABELS_MESSAGE', 'srm_testimonial_labels_message' );
        // Funnel Meta Data.
        define( 'SRM_META_REVIEWER_NAME', '_srm_reviewer_name' );
        define( 'SRM_META_REVIEWER_EMAIL', '_srm_reviewer_email' );
        define( 'SRM_META_REVIEWER_PHONE', '_srm_reviewer_phone' );
        define( 'SRM_META_REVIEWER_FEEDBACK', '_srm_feedback' );
        define( 'SRM_META_FUNNEL_ID', '_srm_funnel_id' );
        define( 'SRM_META_DESTI_NAME', '_srm_desti_name' );
        define( 'SRM_META_DESTI_URL', '_srm_destination_url' );
        define( 'SRM_META_DESTI_TYPE', '_srm_desti_type' );
        define( 'SRM_META_FEEDBACK_ID', '_srm_feedback' );
        define( 'SRM_META_EMAIL_FEEDBACK', '_srm_email_feedback' );
        define( 'SRM_META_TRACKING_ID', '_srm_tracking_id' );
        // Testimonial Meta Data.
        define( 'SRM_META_TESTIMONIAL_REFERRER', '_srm_testimonial_referrer' );
        define( 'SRM_META_TESTIMONIAL_HIDDEN', '_srm_testimonial_hidden' );
        define( 'SRM_META_TESTIMONIAL_RATING', '_srm_testimonial_rating' );
        define( 'SRM_META_TESTIMONIAL_NAME', '_srm_testimonial_name' );
        define( 'SRM_META_TESTIMONIAL_EMAIL', '_srm_testimonial_email' );
        define( 'SRM_META_TESTIMONIAL_PHONE', '_srm_testimonial_phone' );
        define( 'SRM_META_TESTIMONIAL_MESSAGE', '_srm_testimonial_message' );
        define( 'SRM_META_TESTIMONIAL_AVATAR', '_srm_testimonial_avatar' );
        // Plugin Migrations!
        define( 'SRM_MIGRATIONS', 'srm_migrations' );
        // Plugin Dialogues.
        define( 'SRM_DIALOGUE_HELP_FUNNEL', SRM_PLUGIN_PATH . 'inc/dialogues/starfish-help-funnel.dialogue.php' );
        define( 'SRM_DIALOGUE_HELP_SETTINGS', SRM_PLUGIN_PATH . 'inc/dialogues/starfish-help-settings.dialogue.php' );
        define( 'SRM_DIALOGUE_REVIEW_DETAILS', SRM_PLUGIN_PATH . 'inc/dialogues/starfish-review-details.dialogue__premium_only.php' );
        define( 'SRM_DIALOGUE_REVIEWS_PROFILES', SRM_PLUGIN_PATH . 'inc/dialogues/starfish-reviews-profiles.dialogue__premium_only.php' );
        // User Meta Keys.
        define( 'SRM_ADMIN_NOTICE_HOW_STARFISH_WORKS_ALERT', 'srm_how_starfish_works_alert' );
        define( 'SRM_ADMIN_NOTICE_FEEDBACK_NEW_ALERT', 'srm_feedback_new_alert' );
        define( 'SRM_ADMIN_NOTICE_FUNNELS_COUNT_ALERT', 'srm_funnels_count_alert' );
        define( 'SRM_ADMIN_NOTICE_COLLECTIONS_COUNT_ALERT', 'srm_collections_count_alert' );
        define( 'SRM_ADMIN_NOTICE_PROFILES_COUNT_ALERT', 'srm_profiles_count_alert' );
        define( 'SRM_ADMIN_NOTICE_FUNNELS_COUNT_VIOLATIONS_ALERT', 'srm_funnels_count_violations_alert' );
        define( 'SRM_ADMIN_NOTICE_COLLECTIONS_COUNT_VIOLATIONS_ALERT', 'srm_collections_count_violations_alert' );
        define( 'SRM_ADMIN_NOTICE_PROFILES_COUNT_VIOLATIONS_ALERT', 'srm_profiles_count_violations_alert' );
        // Transients.
        define( 'SRM_TRAN_CRON_LAST_PROFILE_SCRAPE_DATE', 'srm_cron_last_profile_scrape_run' );
        define( 'SRM_TRAN_PLAN_LIMITS', 'srm_plan_limits' );
        define( 'SRM_TRAN_RICHREVIEWS_MIGRATION', 'srm_richreviews_migration' );
    }
    
    /**
     * Included Actions
     */
    public static function srm_actions()
    {
        // Initial.
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-feedback-process.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-admin-notices.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-funnels.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-testimonials.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-feedback.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-pluginloaded.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-enqueue-scripts.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-templates.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/options/starfish-admin-options.action.php';
        // Admin Menus.
        require_once SRM_PLUGIN_PATH . 'init/actions/menu/starfish-admin-menu-parent.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/menu/starfish-admin-menu-settings.action.php';
        // API.
        require_once SRM_PLUGIN_PATH . 'init/actions/api/starfish-api-meta.action.php';
        // AJAX Callbacks.
        require_once SRM_PLUGIN_PATH . 'init/actions/ajax/starfish-ajax-callbacks.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/ajax/starfish-ajax-callbacks-testimonial.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/ajax/starfish-ajax-callbacks-richreviews-migrate.action.php';
        // General.
        require_once SRM_PLUGIN_PATH . 'init/actions/menu/starfish-admin-menu-bar.action.php';
        require_once SRM_PLUGIN_PATH . 'init/actions/starfish-footer.action.php';
    }
    
    /**
     * Included Filters
     */
    public static function srm_filters()
    {
        require_once SRM_PLUGIN_PATH . 'init/filters/starfish-action-links.filter.php';
        require_once SRM_PLUGIN_PATH . 'init/filters/starfish-feedback-admin.filter.php';
        require_once SRM_PLUGIN_PATH . 'init/filters/starfish-api.filter.php';
        require_once SRM_PLUGIN_PATH . 'init/filters/starfish-admin-funnel-columns.filter.php';
        require_once SRM_PLUGIN_PATH . 'init/filters/starfish-testimonials.filter.php';
        require_once SRM_PLUGIN_PATH . 'init/filters/starfish-admin-testimonial-columns.filter.php';
    }
    
    /**
     * Basic Includes
     */
    public static function srm_includes()
    {
    }
    
    /**
     * Included Shortcodes
     */
    public static function srm_shortcodes()
    {
        require_once SRM_PLUGIN_PATH . 'init/shortcodes/starfish-main.shortcode.php';
    }
    
    /**
     * Initialize localization
     */
    public static function load_plugin_languages()
    {
        // load plugin languages.
        load_plugin_textdomain( 'starfish', false, SRM_PLUGIN_PATH . '/languages/' );
    }

}