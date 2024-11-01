<?php

namespace Starfish;

use Freemius_Exception;
use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Cron as SRM_CRON;
use Starfish\Logging as SRM_LOGGING;
use Starfish\Migrations as SRM_MIGRATIONS;
use Starfish\Twig as SRM_TWIG;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

use function add_settings_field;
use function add_settings_section;
use function esc_html__;
use function file_exists;
use function file_get_contents;
use function register_setting;
use function sprintf;

use const SRM_LOG_FILE;
use const SRM_OPTION_CLEAN_ON_DEACTIVATE;
use const SRM_OPTION_FUNNEL_SLUG;
use const SRM_OPTION_GROUP_GENERAL;
use const SRM_OPTION_GROUP_REVIEWS;
use const SRM_OPTION_LOGGING;
use const SRM_OPTION_SUPPORT_ALERTS;
use const SRM_OPTION_REVIEW_SCRAPE_CRON;

/**
 * SETTINGS
 *
 * Main class for managing Starfish Settings instance
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1.0
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://codex.wordpress.org/Settings_API
 * @since      2.0
 */
class Settings
{
    // Premium Only Styling
    private $premium_only;

    private $general_options;

    private $general_migrations_options;

    private $funnel_general_options;

    private $funnel_email_options;

    private $reviews_options;

    private $testimonials_general_options;

    private $testimonials_form_options;

    private $testimonials_email_options;

    private $support_options;

    /**
     * @throws Freemius_Exception
     */
    public function __construct()
    {
        $this->premium_only         		= SRM_FREEMIUS::starfish_premium_only_styles();
        $this->general_options      		= self::srm_get_options('general');
        $this->funnel_general_options    	= self::srm_get_options('funnel_general');
        $this->funnel_email_options      	= self::srm_get_options('funnel_email');
        $this->reviews_options      		= self::srm_get_options('reviews');
        $this->testimonials_general_options = self::srm_get_options('testimonial_general');
        $this->testimonials_form_options 	= self::srm_get_options('testimonial_form');
        $this->testimonials_email_options 	= self::srm_get_options('testimonial_email');
        $this->support_options      		= self::srm_get_options('support');

        // Register Settings
        add_action('admin_init', array( $this, 'register_settings' ));
        // Add Sections
        add_action('admin_init', array( $this, 'section_settings' ));
        // Clear permalinks
        add_action('shutdown', 'flush_rewrite_rules');
    }

    /**
     * Get Options
     */
    public static function srm_get_options($type)
    {
        switch ($type) {
            case 'general':
                return array(
                    SRM_OPTION_HIDE_BRANDING       => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Branding on Funnels and Collections.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => 'no',
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_CLEAN_ON_DEACTIVATE => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Delete all settings and data on deactivation.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => 'no',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_AFFILIATE_URL       => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Override "Powered by Starfish" Link', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => __('Powered by <a href="https://starfish.reviews" title="Review Generation on WordPress" target="_blank" rel="nofollow">Starfish Reviews</a>', 'starfish'),
                        'is_premium'        => true,
                    ),
                );
            case 'general_migrations':
                return null;
            case 'funnel_general':
                return array(
                    SRM_OPTION_FUNNEL_ARCHIVES     => array(
                        'type'              => 'boolean',
                        'description'       => esc_html__('Disable the Funnel Archive page', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => true,
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_FUNNEL_SLUG         => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Change the Funnel Slug', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => 'funnel',
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Text for the link to skip the feedback form', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Skip this feedback and leave a public review.', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_skip_feedback_text',
                    ),
                    SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU           => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The message shown after submitting Feedback.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Thanks for your feedback. We\'ll review it and work to improve.', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_no_thank_you_msg',
                    ),
                    SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The message shown above the Feedback form.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('We\'re sorry we didn\'t meet expectations. How can we do better in the future?', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_no_review_prompt',
                    ),
                    SRM_OPTION_FUNNEL_REVIEW_PROMPT      => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The message shown above the destination options.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('<h3>Excellent! Please Rate us 5-stars</h3>...and leave a helpful review.', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_yes_review_prompt',
                    ),
                    SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO   => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The feedback submit button text.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Send Feedback', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_button_text_no',
                    ),
                    SRM_OPTION_FUNNEL_SUBMIT_BUTTON      => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The button text for submitting a review on single destination funnels.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Submit Review', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_button_text',
                    ),
                    SRM_OPTION_FUNNEL_YN_QUESTION        => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The main funnel question text.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Would you recommend {site-name}?', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_yn_question',
                    ),
                    SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The text for leaving a public review after submitting feedback.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Leave a Public Review', 'starfish'),
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_public_review_text',
                    ),
                    SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS       => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The number of seconds to wait before auto-redirecting to the single destination (0 = disabled; "imm" = immediately).', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => '0',
                        'is_premium'        => true,
                        'funnel_cf_id'      => '_srm_review_auto_redirect',
                    ),
                );
            case 'funnel_email':
                return array(
                    SRM_OPTION_FUNNEL_REPLY_TO_EMAIL         => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Use submitter ID as "reply-to" email address.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => 'yes',
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_NAME        => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The name the email will appear to be from.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => '{site-name}',
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_EMAIL       => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Email address the message will appear to be from.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => '{admin-email}',
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_SUBJECT          => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Subject of the email.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Feedback from {funnel-name}', 'starfish'),
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_CONTENT         => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Contents of the message.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => esc_html__('Submitter ID: {tracking-id}<br/>Message: {review-message}<br/>{reviewer-name}<br/>{reviewer-email}<br/>{reviewer-phone}', 'starfish'),
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The email address which feedback is sent to.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => '{admin-email}',
                        'is_premium'        => false,
                    ),
                );
            case 'reviews':
                return array(
                    SRM_OPTION_REVIEW_SCRAPE_CRON => array(
                        'type'              => 'string',
                        'description'       => esc_html__('How often will Starfish Scrape existing profiles for new reviews', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => SRM_CRON::CRON_SCHEDULES['MONTHLY'],
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_REVIEW_SCRAPE_PULL_TIME => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Define how many hours before the option to pull reviews from the last scrape attempt is available.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => '12',
                        'is_premium'        => true,
                    ),
                );
            case 'testimonial_general':
                return array(
                    SRM_OPTION_TESTIMONIAL_REQUIRE_LOGIN => array(
                        'type'              => 'boolean',
                        'description'       => esc_html__('Require a user to be logged in to submit a testimonial.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => false,
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_NOT_AUTHENTICATED_MESSAGE => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The message returned if the user is not logged in.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Please login.',
                        'is_premium'        => false,
                    ),
                );
            case 'testimonial_email':
                return array(
                    SRM_OPTION_TESTIMONIAL_ENABLE_EMAIL_NOTIFICATION => array(
                        'type'              => 'boolean',
                        'description'       => esc_html__('Send an email notification when a new testimonial is received.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => true,
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_NAME => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The name the recipient sees the notification being from (requires Email Notifications to be enabled).', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => '{site-name}',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_EMAIL => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The email address the recipient sees the notification being from (requires Email Notifications to be enabled).', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => '{admin-email}',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_RECIPIENT => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The email address notifications of newly submitted testimonials will be sent to (requires Email Notifications to be enabled).', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => '{admin-email}',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_CONTENT => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The content sent in the email notification (requires Email Notifications to be enabled. Text Only.).  Supports the following shortcodes: {site-name}, {site-url}, {testimonial-all}, {testimonial-rating}, {testimonial-author}, {testimonial-message}, {testimonial-phone}, {testimonial-email}, {testimonial-referrer}', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'A new Testimonial has been received on {site-name}',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_SUBJECT => array(
                        'type'              => 'string',
                        'description'       => esc_html__('The email notification subject line (requires Email Notifications to be enabled. Text Only.).  Supports the following shortcodes: {site-name}, {testimonial-rating}, {testimonial-author}, {testimonial-phone}, {testimonial-email}', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'A new Testimonial has been received on {site-name}',
                        'is_premium'        => false,
                    )
                );
            case 'testimonial_form':
                return array(
                    SRM_OPTION_TESTIMONIAL_FORM_SUCCESS => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Successfully submitted Testimonial, Thank you!',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_FORM_INSTRUCTIONS => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Please rate us.',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_ERROR_RATING => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Rating is a required field.',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_ERROR_NAME => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Name is a required field.',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_ERROR_EMAIL => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Email is a required field.',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_ERROR_PHONE => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Phone is a required field.',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_ERROR_MESSAGE => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Message is a required field.',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_LABELS_CATEGORY => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Subject',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_LABELS_NAME => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Name',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_LABELS_EMAIL => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Email',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_LABELS_PHONE => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Phone',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_LABELS_RATING => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Rating',
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_TESTIMONIAL_LABELS_MESSAGE => array(
                        'type'              => 'string',
                        'description'       => esc_html__('', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => 'Message',
                        'is_premium'        => false,
                    ),
                );
            case 'support':
                return array(
                    SRM_OPTION_LOGGING        => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Enable to capture available logs for troubleshooting issues.', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => false,
                        'is_premium'        => false,
                    ),
                    SRM_OPTION_SUPPORT_ALERTS => array(
                        'type'              => 'string',
                        'description'       => esc_html__('Send the Starfish Reviews support team notifications of specific failures (No private or sensitive information is gathered).', 'starfish'),
                        'sanitize_callback' => null,
                        'show_in_rest'      => false,
                        'default'           => true,
                        'is_premium'        => false,
                    ),
                );
            default:
                return array();
        }
    }

    /**
     * Register Settings
     */
    public function register_settings()
    {
        foreach ($this->general_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_GENERAL, $option, $args);
        }

        foreach ($this->funnel_general_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_FUNNEL_GENERAL, $option, $args);
        }

        foreach ($this->funnel_email_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_FUNNEL_EMAIL, $option, $args);
        }

        foreach ($this->reviews_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_REVIEWS, $option, $args);
        }

        foreach ($this->testimonials_general_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_TESTIMONIAL_GENERAL, $option, $args);
        }

        foreach ($this->testimonials_form_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_TESTIMONIAL_FORM, $option, $args);
        }

        foreach ($this->testimonials_email_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_TESTIMONIAL_EMAIL, $option, $args);
        }

        foreach ($this->support_options as $option => $args) {
            register_setting(SRM_OPTION_GROUP_SUPPORT_GENERAL, $option, $args);
        }

    }

    /**
     * Add Setting Sections
     */
    public function section_settings()
    {
        // General Section.
        add_settings_section(
            'srm_general_settings_section', // ID
            esc_html__('General', 'starfish'), // Title
            array( $this, 'print_general_section_info' ), // Callback
            SRM_OPTION_GROUP_GENERAL // Page
        );
        add_settings_section(
            'srm_general_migrations_settings_section', // ID
            esc_html__('Migrations', 'starfish'), // Title
            array( $this, 'print_general_migrations_section_info' ), // Callback
            SRM_OPTION_GROUP_GENERAL_MIGRATIONS // Page
        );

        // Funnels Section.
        add_settings_section(
            'srm_funnel_settings_general_section', // ID
            esc_html__('General ', 'starfish'), // Title
            array( $this, 'print_funnel_general_section_info'), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL // Page
        );
        add_settings_section(
            'srm_funnel_settings_email_section', // ID
            esc_html__('Email ', 'starfish'), // Title
            array( $this, 'print_funnel_email_section_info'), // Callback
            SRM_OPTION_GROUP_FUNNEL_EMAIL // Page
        );

        // Reviews Section.
        add_settings_section(
            'srm_reviews_settings_section', // ID
            esc_html__('Reviews ', 'starfish'), // Title
            array( $this, 'print_reviews_section_info' ), // Callback
            SRM_OPTION_GROUP_REVIEWS // Page
        );

        // Testimonials Section.
        add_settings_section(
            'srm_testimonials_settings_general_section', // ID
            esc_html__('General ', 'starfish'), // Title
            array( $this, 'print_testimonials_general_section_info' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_GENERAL // Page
        );
        add_settings_section(
            'srm_testimonials_settings_form_section', // ID
            esc_html__('Form ', 'starfish'), // Title
            array( $this, 'print_testimonials_form_section_info' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM // Page
        );
        add_settings_section(
            'srm_testimonials_settings_email_section', // ID
            esc_html__('Email ', 'starfish'), // Title
            array( $this, 'print_testimonials_email_section_info' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_EMAIL // Page
        );
        add_settings_section(
            'srm_testimonials_settings_help_section', // ID
            esc_html__('Help ', 'starfish'), // Title
            array( $this, 'print_testimonials_help_section_info' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_HELP // Page
        );

        // Support Section.
        add_settings_section(
            'srm_support_settings_general_section', // ID
            esc_html__('Support ', 'starfish'), // Title
            array( $this, 'print_support_general_section_info' ), // Callback
            SRM_OPTION_GROUP_SUPPORT_GENERAL // Page
        );

        add_settings_section(
            'srm_support_settings_console_section', // ID
            esc_html__('Log Console ', 'starfish'), // Title
            array( $this, 'print_support_console_section_info' ), // Callback
            SRM_OPTION_GROUP_SUPPORT_CONSOLE // Page
        );

        add_settings_section(
            'srm_support_settings_info_section', // ID
            esc_html__('Site Information ', 'starfish'), // Title
            array( $this, 'print_support_info_section_info' ), // Callback
            SRM_OPTION_GROUP_SUPPORT_INFO // Page
        );

        // Add Settings Fields by section.
        $this->general_settings_fields();
        $this->funnels_settings_fields();
        $this->reviews_settings_fields();
        $this->testimonials_settings_fields();
        $this->support_settings_fields();
    }

    public function support_settings_fields()
    {
        add_settings_field(
            SRM_OPTION_LOGGING, // ID
            esc_html__('Enable Logging', 'starfish'), // Title
            array( $this, 'srm_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_SUPPORT_GENERAL, // Page
            'srm_support_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_LOGGING,
                'description' => esc_html__('Capture available logs to help troubleshoot issues. MAKE SURE TO DISABLE WHEN FINISHED! The log file can get large and potentially impact plugin performance.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_SUPPORT_ALERTS, // ID
            esc_html__('Enable Notifications', 'starfish'), // Title
            array( $this, 'srm_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_SUPPORT_GENERAL, // Page
            'srm_support_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_SUPPORT_ALERTS,
                'description' => __('Send the Starfish Reviews support team notifications of specific failures (No private or sensitive information is gathered). ', 'starfish'),
            )
        );
    }

    public function general_settings_fields()
    {
        add_settings_field(
            SRM_OPTION_HIDE_BRANDING, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Disable Branding', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_GENERAL, // Page
            'srm_general_settings_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_HIDE_BRANDING,
                'description' => __('Branding on Funnels & Collections.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_AFFILIATE_URL, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Branding Content', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_GENERAL, // Page
            'srm_general_settings_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_AFFILIATE_URL,
                'description' => __('Insert HTML for your branding. Consider joining', 'starfish') . ' <a href="https://starfish.reviews/affiliate/" title="Starfish\'s affiliate program" target="_blank">Starfish\'s affiliate program</a> <i class="fas fa-external-link-alt"></i>' . __(' and inserting your affiliate link here.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_CLEAN_ON_DEACTIVATE, // ID
            esc_html__('Clean On Deactivation', 'starfish'), // Title
            array( $this, 'srm_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_GENERAL, // Page
            'srm_general_settings_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_CLEAN_ON_DEACTIVATE,
                'description' => __('DO NOT use this option, unless you want to remove ALL Starfish Reviews settings and data.', 'starfish'),
            )
        );
    }

    public function funnels_settings_fields()
    {
        // Feedback Email Settings
        add_settings_field(
            SRM_OPTION_FUNNEL_REPLY_TO_EMAIL, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Reply-To ID', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_EMAIL, // Page
            'srm_funnel_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_REPLY_TO_EMAIL,
                'description' => __('Set the Funnel\'s <code>id</code> parameter as the Feedback email message\'s Reply-To value (Will only be used if the <code>id</code> value is a properly formatted email address).', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_NAME, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('From Name', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_EMAIL, // Page
            'srm_funnel_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_NAME,
                'description' => __('Supports shortcode <code>{site-name}</code> to use the site\'s name.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_EMAIL, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('From Email', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_EMAIL, // Page
            'srm_funnel_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_FROM_EMAIL,
                'description' => __('Supports shortcode <code>{admin-email}</code> to use the site\'s Administrator\'s email.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_SUBJECT, // ID
            esc_html__('Email Subject', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_EMAIL, // Page
            'srm_funnel_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_SUBJECT,
                'description' => __('Supports shortcode <code>{funnel-name}</code> and <code>{tracking-id}</code>.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Email Recipient', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_EMAIL, // Page
            'srm_funnel_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT,
                'description' => __('Supports shortcode <code>{admin-email}</code> to use the site\'s Administrator\'s email.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_CONTENT, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Email Message', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_EMAIL, // Page
            'srm_funnel_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_CONTENT,
                'description' => __('Make sure to include shortcode <code>{review-message}</code>. Also supports <code>{funnel-name}</code>, <code>{tracking-id}</code>, <code>{reviewer-name}</code>, <code>{reviewer-email}</code>, and <code>{reviewer-phone}</code>.', 'starfish'),
            )
        );
        // General Funnel Settings
        add_settings_field(
            SRM_OPTION_FUNNEL_SLUG, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Funnel Slug', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_SLUG,
                'description' => __('The Funnel post-type slug. Note: Re-save <a href=/wp-admin/options-permalink.php" title="Settings Permalink">Permalinks</a> if your funnel goes to a 404 page.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_ARCHIVES, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Disable Funnel Archives', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_ARCHIVES,
                'description' => __('Disable the Funnel Archives page', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Feedback Thank You Message', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU,
                'description' => esc_html__('Message displayed after feedback has been submitted on a "No" response.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Feedback Prompt', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT,
                'description' => esc_html__('Message displayed after submitting a "No" response.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_REVIEW_PROMPT, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Review Prompt', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_REVIEW_PROMPT,
                'description' => esc_html__('Message displayed after submitting a "Yes" response.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Submit Button Text on "No"', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO,
                'description' => esc_html__('When a user selects "NO" this text will be used on the Submit Button.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_SUBMIT_BUTTON, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Submit Button Text on "Yes"', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_SUBMIT_BUTTON,
                'description' => esc_html__('When a user selects "YES" this text will be used on the Submit Button.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_YN_QUESTION, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Yes/No Question', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_YN_QUESTION,
                'description' => esc_html__('The prompt to recommend this website or not.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Public Review Text', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT,
                'description' => esc_html__('The text shown above the button(s) to leave a review after skipping negative feedback', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Yes Redirect Seconds', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS,
                'description' => esc_html__('How many seconds to wait before redirecting to the review site.', 'starfish'),
            )
        );

        add_settings_field(
            SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Skip Feedback Link Text', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_FUNNEL_GENERAL, // Page
            'srm_funnel_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT,
                'description' => esc_html__('The link text for skipping the feedback form (Requires the Funnel \'Disable Review Gating\' to be disabled).', 'starfish'),
            )
        );
    }

    public function reviews_settings_fields()
    {
        add_settings_field(
            SRM_OPTION_REVIEW_SCRAPE_CRON, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Scrape Schedule', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_reviews_scrape_cron_select_callback' ), // Callback
            SRM_OPTION_GROUP_REVIEWS, // Page
            'srm_reviews_settings_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_REVIEW_SCRAPE_CRON,
                'description' => esc_html__('Choose how often profiles will be scraped for reviews.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_REVIEW_SCRAPE_PULL_TIME, // ID
            $this->premium_only['premium_only_badge'] . esc_html__('Pull Reviews Delay', 'starfish') . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_input_field' ), // Callback
            SRM_OPTION_GROUP_REVIEWS, // Page
            'srm_reviews_settings_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_REVIEW_SCRAPE_PULL_TIME,
                'description' => esc_html__('Define how many hours before the option to pull reviews from the last scrape attempt is available.', 'starfish'),
            )
        );
    }

    public function testimonials_settings_fields()
    {
        // Testimonial General Settings
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_REQUIRE_LOGIN, // ID
            esc_html__('Require Login', 'starfish'), // Title
            array( $this, 'srm_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_GENERAL, // Page
            'srm_testimonials_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_REQUIRE_LOGIN,
                'description' => esc_html__('Require user to be logged in before submitting a testimonial.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_NOT_AUTHENTICATED_MESSAGE, // ID
            esc_html__('Not Logged In Message', 'starfish'), // Title
            array( $this, 'srm_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_GENERAL, // Page
            'srm_testimonials_settings_general_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_NOT_AUTHENTICATED_MESSAGE,
                'description' => esc_html__('Brief message shown if a user is not logged in when viewing the Testimonial Form. Text Only.', 'starfish'),
            )
        );
        // Testimonial Email Settings
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_ENABLE_EMAIL_NOTIFICATION, // ID
            esc_html__('Enable Email Notifications', 'starfish'), // Title
            array( $this, 'srm_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_EMAIL, // Page
            'srm_testimonials_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_ENABLE_EMAIL_NOTIFICATION,
                'description' => esc_html__('Send an email notification when a new testimonial is received.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_NAME, // ID
            esc_html__('From Name', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_EMAIL, // Page
            'srm_testimonials_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_NAME,
                'description' => __('The name the recipient sees the notification being from (requires Email Notifications to be enabled).<br/>Supports the following shortcodes: <code>{site-name}</code>', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_EMAIL, // ID
            esc_html__('From Email', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_EMAIL, // Page
            'srm_testimonials_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_FROM_EMAIL,
                'description' => __('The email address the recipient sees the notification being from (requires Email Notifications to be enabled).<br/>Supports the following shortcodes: <code>{admin-email}</code>', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_RECIPIENT, // ID
            esc_html__('Notification Recipient', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_EMAIL, // Page
            'srm_testimonials_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_RECIPIENT,
                'description' => __('The email address notifications of newly submitted testimonials will be sent to (requires Email Notifications to be enabled).<br/>Supports the following shortcodes: <code>{admin-email}</code>', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_SUBJECT, // ID
            esc_html__('Notification Subject', 'starfish'), // Title
            array( $this, 'srm_get_input_field_large' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_EMAIL, // Page
            'srm_testimonials_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_SUBJECT,
                'description' => __('The email notification subject line (requires Email Notifications to be enabled. Text Only).<br/>Supports the following shortcodes: <code>{site-name}</code>, <code>{testimonial-rating}</code>, <code>{testimonial-author}</code>, <code>{testimonial-phone}</code>, <code>{testimonial-email}</code>', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_CONTENT, // ID
            esc_html__('Notification Content', 'starfish'), // Title
            array( $this, 'srm_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_EMAIL, // Page
            'srm_testimonials_settings_email_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_EMAIL_NOTIFICATION_CONTENT,
                'description' => __('The content sent in the email notification (requires Email Notifications to be enabled).<br/>Supports the following shortcodes: <code>{site-name}</code>, <code>{site-url}</code>, <code>{testimonial-all}</code>, <code>{testimonial-rating}</code>, <code>{testimonial-author}</code>, <code>{testimonial-message}</code>, <code>{testimonial-phone}</code>, <code>{testimonial-email}</code>, <code>{testimonial-referrer}</code>', 'starfish'),
            )
        );
        // Testimonial Form Settings
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_FORM_SUCCESS, // ID
            esc_html__('Success Message', 'starfish'), // Title
            array( $this, 'srm_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_FORM_SUCCESS,
                'description' => esc_html__('Brief message shown after submitting a new Testimonial. Text Only.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_FORM_INSTRUCTIONS, // ID
            esc_html__('Form Instructions', 'starfish'), // Title
            array( $this, 'srm_get_textarea_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_FORM_INSTRUCTIONS,
                'description' => esc_html__('Brief instructions at the top of the Testimonial Form. Text Only. Leave blank to remove instructions.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_ERROR_RATING, // ID
            esc_html__('Form Error: Rating Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_ERROR_RATING,
                'description' => esc_html__('Error message displayed when invalid data is submitted, must be marked as required.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_ERROR_NAME, // ID
            esc_html__('Form Error: Name Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_ERROR_NAME,
                'description' => esc_html__('Error message displayed when invalid data is submitted, must be marked as required.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_ERROR_EMAIL, // ID
            esc_html__('Form Error: Email Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_ERROR_EMAIL,
                'description' => esc_html__('Error message displayed when invalid data is submitted, must be marked as required.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_ERROR_PHONE, // ID
            esc_html__('Form Error: Phone Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_ERROR_PHONE,
                'description' => esc_html__('Error message displayed when invalid data is submitted, must be marked as required.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_ERROR_MESSAGE, // ID
            esc_html__('Form Error: Message Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_ERROR_MESSAGE,
                'description' => esc_html__('Error message displayed when invalid data is submitted, must be marked as required.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_LABELS_CATEGORY, // ID
            esc_html__('Form Label: Category Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_LABELS_CATEGORY,
                'description' => esc_html__('Form field label. This field is readonly and is the value provided in the shortcode (i.e., [starfish testimonial="My Category"])', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_LABELS_NAME, // ID
            esc_html__('Form Label: Name Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_LABELS_NAME,
                'description' => esc_html__('Form field label.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_LABELS_EMAIL, // ID
            esc_html__('Form Label: Email Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_LABELS_EMAIL,
                'description' => esc_html__('Form field label.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_LABELS_PHONE, // ID
            esc_html__('Form Label: Phone Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_LABELS_PHONE,
                'description' => esc_html__('Form field label.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_LABELS_RATING, // ID
            esc_html__('Form Label: Rating Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_LABELS_RATING,
                'description' => esc_html__('Form field label.', 'starfish'),
            )
        );
        add_settings_field(
            SRM_OPTION_TESTIMONIAL_LABELS_MESSAGE, // ID
            esc_html__('Form Label: Message Field', 'starfish'), // Title
            array( $this, 'srm_get_input_field' ), // Callback
            SRM_OPTION_GROUP_TESTIMONIAL_FORM, // Page
            'srm_testimonials_settings_form_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_TESTIMONIAL_LABELS_MESSAGE,
                'description' => esc_html__('Form field label.', 'starfish'),
            )
        );
    }

    public function print_general_section_info($args)
    {
        print sprintf(__('Specify %1$s settings below', 'starfish'), $args['title']);
        include SRM_PLUGIN_PATH . 'inc/starfish-settings-communitybar.inc.php';
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function print_general_migrations_section_info($args)
    {
        print sprintf(__('Manually execute %1$s below', 'starfish'), $args['title']);

		echo '<p>COMING SOON!</p>';
//        $schema  	 = SRM_MIGRATIONS::get_schema();
//        $directories = glob(SRM_PLUGIN_PATH . 'migrations/*', GLOB_ONLYDIR);
//        $migrations  = array();
//        foreach ($directories as $directory) {
//            $config = $directory . '/config.json';
//            if (file_exists($config)) {
//                $migrations[] = json_decode(file_get_contents($config));
//
//            }
//        }
//        $template_variables = [
//            'schema' 	 => $schema,
//            'migrations' => $migrations
//        ];
//        $twig = SRM_TWIG::get_twig();
//        echo $twig->render('starfish-admin-settings-migration.html.twig', $template_variables);
    }

    public function print_funnel_general_section_info($args)
    {
        $introduction = sprintf(
            /* translators: %1$s is replaced with the Setting's section */
            __('Specify the default %1$s settings below. These will apply when creating new <a href="%2$s" title="View Funnels">Funnels</a>.', 'starfish'),
            $args['title'],
            '/wp-admin/edit.php?post_type=funnel'
        );
        $sidebar_title_toolbox = __('Settings Toolbox', 'starfish');
        $toolbox_button_text   = __('Restore System Defaults', 'starfish');
        $description           = __('Replace all existing values with the system defaults.', 'starfish');
        print <<<D_SECTION
	<div class="srm-settings-info">$introduction</div>
	<div class="bootstrap-srm">
		<div class="srm-settings-sidebar">
			<div class="srm-settings-sidebar-title"><span class="fas fa-wrench"></span> $sidebar_title_toolbox</div>
			<div id="srm-settings-tool-restore" class="srm-settings-tool">
					<button id="srm-defaults-restore" class="srm-settings-caution-button srm-settings-button">$toolbox_button_text</button>
					<p class="description">$description</p>
			</div>
		</div>
	</div>
D_SECTION;
    }

    public function print_funnel_email_section_info($args)
    {
        print sprintf(__('Specify settings below for %1$s', 'starfish'), $args['title']);
    }

    public function print_reviews_section_info($args)
    {
        print sprintf(__('Specify settings below for %1$s', 'starfish'), $args['title']);
    }

    public function print_testimonials_general_section_info($args)
    {
        $introduction = sprintf(
            /* translators: %1$s is replaced with the Setting's section */
            __('Specify %1$s settings below.', 'starfish'),
            $args['title']
        );
        $sidebar_title_toolbox = __('Settings Toolbox', 'starfish');
        $toolbox_button_text   = __('Migrate Rich Reviews', 'starfish');
        $description           = __('Copy all Rich Reviews to Testimonials', 'starfish');

        // Check if Rich Reviews is installed and active.
        if (is_plugin_active('rich-reviews/rich-reviews.php')) {
            print <<<D_SECTION
	<div class="srm-settings-info">$introduction</div>
	<div class="bootstrap-srm">
		<div class="srm-settings-sidebar">
			<div class="srm-settings-sidebar-title"><span class="fas fa-wrench"></span> $sidebar_title_toolbox</div>
			<div id="srm-settings-tool-migrate" class="srm-settings-tool">
					<button id="srm-start-richreviews-migrations" class="srm-settings-caution-button srm-settings-button">$toolbox_button_text</button>
					<p class="description">$description</p>
			</div>
		</div>
	</div>
D_SECTION;
        }
    }

    public function print_testimonials_form_section_info($args)
    {
        print sprintf(__('Specify the Testimonial Form settings below', 'starfish'), $args['title']);
    }

    public function print_testimonials_email_section_info($args)
    {
        print sprintf(__('Specify the Testimonial Email Notification settings below', 'starfish'), $args['title']);
    }

    public function print_testimonials_help_section_info($args)
    {
        print sprintf(__('Instructions for using the testimonial shortcode. Each section represents an attribute of the <code>[starfish attribute=""]</code> shortcode', 'starfish'), $args['title']);
        include SRM_PLUGIN_PATH . 'inc/starfish-settings-testimonial-instructions.inc.php';
    }

    public function print_support_general_section_info($args)
    {
        print sprintf(__('Specify %1$s settings below', 'starfish'), $args['title']);
    }

    public function print_support_console_section_info($args)
    {

        print sprintf(__('Specify %1$s settings below', 'starfish'), $args['title']);
        $send_logs_visibility = 'style="display: none"';
        $log_contents         = null;
        $file_size			  = null;
        $log_size_message	  = null;
        // Logging console.
        if (file_exists(SRM_LOG_FILE) && !empty(file_get_contents(SRM_LOG_FILE))) {
            $file      = file(SRM_LOG_FILE);
            $file_size = SRM_LOGGING::human_filesize(filesize(SRM_LOG_FILE));
            if($file_size) {
                $log_size_message = 'log size: '.$file_size.' (refresh page to update)';
            }
            $reverse   = array_reverse($file);
            foreach ($reverse as $line) {
                $log_contents .= $line;
            }
        } else {
            $log_contents = 'No Logs Available.';
        }
        if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
            $send_logs_visibility = '';
        }
        echo '<div id="srm-logging-console" class="bootstrap-srm">
				    <div id="srm-logging-actions">
				        <div id="srm-refresh-logs-button" class="button button-secondary" title="Refresh Logs"><i class="fa fa-sync"></i></div>
				        <div id="srm-purge-logs-button" class="button button-secondary" title="Purge Logs"><i class="fa fa-trash"></i></div>
				        <div id="srm-send-logs-button" class="button button-secondary" title="Send available logs to Starfish Support" ' . $send_logs_visibility . '><i class="fa fa-paper-plane"></i></div>
				    </div>
				    <small>' . $log_size_message . '</small>				    
				    <div id="srm-logging-content"><pre>' . $log_contents . '</pre></div>
				</div>';
    }

    public function print_support_info_section_info($args)
    {
        print __('Details from the current Starfish Reviews install. Useful information for troubleshooting problems or generally understanding the environment which Starfish Reviews recognizes.', 'starfish');

        $card_header  = __('Plan Limits', 'starfish');
        $card_text  = __('Shows the current limits based on the ', 'starfish') . '<a href="/admin.php?page=starfish-settings-account">' . __('active license.', 'starfish') . '</a>';

        $plan_limits = get_transient(SRM_TRAN_PLAN_LIMITS);
        $plan_title  = __('Active Plan: ', 'starfish') . SRM_PLAN_TITLE . ' (<a href="/admin.php?page=starfish-settings-pricing">' . __('Upgrade?', 'starfish') . '</a>)';

        echo <<<SITE
		<div class='bootstrap-srm'>
		<div class="card srm-support-site-card" style="width: 20rem; padding:0">
			<h5 class="card-header">{$card_header} <a id="srm-support-site-limit-refresh" href="#" class="srm-settings-siteinfo-button btn btn-sm btn-outline-primary">Refresh</a></h5>
<!--		  <img class="card-img-top" src="..." alt="Card image cap">-->
			<div class="card-body">
				<p class="card-text">{$card_text}</p>
				<ul>
					<li id="srm-support-site-planlimit-collections">Collections: {$plan_limits['collections']}</li>
					<li id="srm-support-site-planlimit-profiles">Profiles: {$plan_limits['profiles']}</li>
					<li id="srm-support-site-planlimit-funnels">Funnels: {$plan_limits['funnels']}</li>
				</ul>
			</div>
			<div class="card-footer text-muted">{$plan_title}</div>
		</div>
		</div>
SITE;

    }

    /**
     * FIELD CALLBACK: Reviews Scrape Cron Schedule
     * TODO: Turn into standard SELECT callback for premium and free settings
     */
    public function srm_reviews_scrape_cron_select_callback($args)
    {
        $current_schedule = esc_html(get_option(SRM_OPTION_REVIEW_SCRAPE_CRON));
        $last_run         = empty(get_transient(SRM_TRAN_CRON_LAST_PROFILE_SCRAPE_DATE)) ? 'Unknown' : get_transient(SRM_TRAN_CRON_LAST_PROFILE_SCRAPE_DATE);
        echo '<div class="' . $this->premium_only['container_class'] . '">';
        echo '<select ' . $this->premium_only['disabled'] . ' name="' . $args['label_for'] . $this->premium_only['container_class'] . '" type="select" id="' . $args['label_for'] . $this->premium_only['container_class'] . '">';
        foreach (SRM_CRON::CRON_SCHEDULES as $key => $schedule) {
            echo '<option ' . selected($schedule, $current_schedule) . ' value="' . $schedule . '">' . ucfirst(strtolower($key)) . '</option>';
        }
        echo '</select>';
        echo '<p class="description" id="' . $args['label_for'] . '-description">' . $args['description'] . '</p>';
        echo '<p>Last Run: ' . $last_run . '</p>';
        echo '</div>';
    }

    /**
     * PREMIUM ONLY
     * CALLBACK: Build premium input field for setting.
     *
     * @param $args
     */
    public function srm_premium_get_input_field($args)
    {
        echo sprintf(
            '<div class="%s"><input  name="%s" type="text" id="%s" value="%s"><p %s class="description" id="%s-description">%s</p></div>',
            $this->premium_only['container_class'],
            $args['label_for'] . $this->premium_only['container_class'], // name
            $args['label_for'] . $this->premium_only['container_class'], // id
            esc_html(get_option($args['label_for'])),
            $this->premium_only['disabled'],
            $args['label_for'],
            $args['description']
        );
    }

    /**
     * PREMIUM ONLY
     * CALLBACK: Build premium checkbox for setting's field.
     *
     * @param $args
     */
    public function srm_premium_get_checkbox_field($args)
    {
        echo sprintf(
            '<div class="%s"><input %s name="%s" type="checkbox" id="%s" %s value="yes"><p class="description" id="%s-description">%s</p></div>',
            $this->premium_only['container_class'],
            $this->premium_only['disabled'],
            $args['label_for'] . $this->premium_only['container_class'], // name
            $args['label_for'] . $this->premium_only['container_class'], // id
            (get_option($args['label_for']) === 'yes' || get_option($args['label_for']) === true) ? 'checked="checked"' : '',
            $args['label_for'],
            $args['description']
        );
    }

    /**
     * CALLBACK: Build premium checkbox for setting's field.
     *
     * @param $args
     */
    public function srm_get_checkbox_field($args)
    {
        echo sprintf(
            '<div><input name="%s" type="checkbox" id="%s" %s value="yes"><p class="description" id="%s-description">%s</p></div>',
            $args['label_for'], // name
            $args['label_for'], // id
            (get_option($args['label_for']) === 'yes' || get_option($args['label_for']) === true) ? 'checked="checked"' : '',
            $args['label_for'],
            $args['description']
        );
    }

    /**
     * PREMIUM ONLY
     * CALLBACK: Build premium textarea field.
     *
     * @param $args
     */
    public function srm_premium_get_textarea_field($args)
    {
        echo sprintf(
            '<textarea class="%s" name="%s" id="%s" rows="7" cols="60" %s>%s</textarea><p class="description" id="%s-description">%s</p>',
            $this->premium_only['container_class'],
            $args['label_for'], // name
            $args['label_for'], // id
            $this->premium_only['disabled'],
            get_option($args['label_for']),
            $args['label_for'],
            $args['description']
        );
    }

    /**
     * CALLBACK: Build textarea field.
     *
     * @param $args
     */
    public function srm_get_textarea_field($args)
    {
        echo sprintf(
            '<textarea name="%s" id="%s" rows="7" cols="60">%s</textarea><p class="description" id="%s-description">%s</p>',
            $args['label_for'],
            $args['label_for'],
            get_option($args['label_for']),
            $args['label_for'],
            $args['description']
        );
    }

    /**
     * CALLBACK: Build input field for setting.
     *
     * @param $args
     */
    public function srm_get_input_field($args)
    {
        echo sprintf(
            '<input name="%s" type="text" id="%s" value="%s"><p class="description" id="%s-description">%s</p>',
            $args['label_for'], // name
            $args['label_for'], // id
            esc_html(get_option($args['label_for'])),
            $args['label_for'],
            $args['description']
        );
    }

    /**
     * CALLBACK: Build LARGE input field for setting.
     *
     * @param $args
     */
    public function srm_get_input_field_large($args)
    {
        echo sprintf(
            '<input name="%s" type="text" id="%s" value="%s" size="100"><p class="description" id="%s-description">%s</p>',
            $args['label_for'], // name
            $args['label_for'], // id
            esc_html(get_option($args['label_for'])),
            $args['label_for'],
            $args['description']
        );
    }
}


if (is_admin() && !class_exists('Settings', true)) {
    return new Settings();
}
