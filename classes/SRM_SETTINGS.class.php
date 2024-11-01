<?php
/**
 * SETTINGS
 *
 * Main class for managing Starfish Settings instance
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0.0
 * @author  silvercolt45 <webmaster@silvercolt.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link https://codex.wordpress.org/Settings_API
 * @since  2.0.0
 */
class SRM_SETTINGS {

	// Premium Only Styling
	private $premium_only;

	private $general_options;

	private $email_options;

	private $defaults_options;

	function __construct() {

		$this->premium_only     = SRM_FREEMIUS::starfish_premium_only_styles();
		$this->general_options  = self::srm_get_options( 'general' );
		$this->email_options    = self::srm_get_options( 'email' );
		$this->defaults_options = self::srm_get_options( 'defaults' );

		// Register Settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Add Sections
		add_action( 'admin_init', array( $this, 'section_settings' ) );
		// Clear permalinks
		add_action( 'shutdown', 'flush_rewrite_rules' );

	}

	/**
	 * Register Settings
	 */
	function register_settings() {

		foreach ( $this->general_options as $option => $args ) {

			register_setting( SRM_OPTION_GROUP_GENERAL, $option, $args );

		}

		foreach ( $this->defaults_options as $option => $args ) {

			register_setting( SRM_OPTION_GROUP_DEFAULTS, $option, $args );

		}

		foreach ( $this->email_options as $option => $args ) {

			register_setting( SRM_OPTION_GROUP_EMAIL, $option, $args );

		}

	}

	/**
	 * Add Setting Sections
	 */
	function section_settings() {

		   // General Section
		add_settings_section(
			'srm_general_settings_section', // ID
			esc_html__( 'General', 'starfish' ), // Title
			array( $this, 'print_general_section_info' ), // Callback
			SRM_OPTION_GROUP_GENERAL // Page
		);

		   // Email Section
		add_settings_section(
			'srm_email_settings_section', // ID
			esc_html__( 'Email', 'starfish' ), // Title
			array( $this, 'print_email_section_info' ), // Callback
			SRM_OPTION_GROUP_EMAIL // Page
		);

		   // Defaults Section
		add_settings_section(
			'srm_defaults_settings_section', // ID
			esc_html__( 'Funnels ', 'starfish' ), // Title
			array( $this, 'print_defaults_section_info' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS // Page
		);

		   // Add Settings Fields by section
		   $this->general_settings_fields();
		   $this->email_settings_fields();
		   $this->defaults_settings_fields();

	}

	/*
	 * Email Settings Fields
	 */
	function email_settings_fields() {

		add_settings_field(
			SRM_OPTION_REPLY_TO_EMAIL, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Reply-To ID', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_checkbox_field' ), // Callback
			SRM_OPTION_GROUP_EMAIL, // Page
			'srm_email_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_REPLY_TO_EMAIL . $this->premium_only['container_class'],
				'description' => __( 'Set the Funnel\'s <code>id</code> parameter as the Feedback email message\'s Reply-To value (Will only be used if the <code>id</code> value is a properly formatted email address).', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_EMAIL_FROM_NAME, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'From Name', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_EMAIL, // Page
			'srm_email_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_EMAIL_FROM_NAME . $this->premium_only['container_class'],
				'description' => esc_html__( 'Supports shortcode {site-name} to use the site\'s name.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_EMAIL_FROM_EMAIL, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'From Email', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_EMAIL, // Page
			'srm_email_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_EMAIL_FROM_EMAIL . $this->premium_only['container_class'],
				'description' => esc_html__( 'Supports shortcode {admin-email} to use the site\'s Administrator\'s email.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_EMAIL_SUBJECT, // ID
			esc_html__( 'Email Subject', 'starfish' ), // Title
			array( $this, 'srm_get_input_field' ), // Callback
			SRM_OPTION_GROUP_EMAIL, // Page
			'srm_email_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_EMAIL_SUBJECT,
				'description' => esc_html__( 'Supports shortcode {funnel-name} and {tracking-id}.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_EMAIL_TEMPLATE, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Message Template', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_textarea_field' ), // Callback
			SRM_OPTION_GROUP_EMAIL, // Page
			'srm_email_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_EMAIL_TEMPLATE . $this->premium_only['container_class'],
				'description' => esc_html__( 'Make sure to include shortcode {review-message}. Also supports {funnel-name}, {tracking-id}, {reviewer-name}, {reviewer-email} and {reviewer-phone}.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_FEEDBACK_EMAIL, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Feedback Email', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_EMAIL, // Page
			'srm_email_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_FEEDBACK_EMAIL . $this->premium_only['container_class'],
				'description' => esc_html__( 'Supports shortcode {admin-email} to use the site\'s Administrator\'s email.', 'starfish' ),
			)
		);

	}

	/*
	 * General Settings Fields
	 */
	function general_settings_fields() {

		add_settings_field(
			SRM_OPTION_FUNNEL_SLUG, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Funnel Slug', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_GENERAL, // Page
			'srm_general_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_FUNNEL_SLUG . $this->premium_only['container_class'],
				'description' => __( 'The Funnel post-type slug. Note: Re-save <a href=/wp-admin/options-permalink.php" title="Settings Permalink">Permalinks</a> if your funnel goes to a 404 page.', 'starfish' ),
			)
		);

        add_settings_field(
            SRM_OPTION_FUNNEL_ARCHIVES, // ID
            $this->premium_only['premium_only_badge'] . esc_html__( 'Disable Funnel Archives', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
            array( $this, 'srm_premium_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_GENERAL, // Page
            'srm_general_settings_section', // Section ID
            array(
                'label_for'   => SRM_OPTION_FUNNEL_ARCHIVES . $this->premium_only['container_class'],
                'description' => __( 'Disable the Funnel Archives page', 'starfish' ),
            )
        );

		add_settings_field(
            SRM_OPTION_HIDE_BRANDING, // ID
            esc_html__( 'Disable Branding', 'starfish' ), // Title
            array( $this, 'srm_premium_get_checkbox_field' ), // Callback
            SRM_OPTION_GROUP_GENERAL, // Page
            'srm_general_settings_section', // Section ID
            array(
				'label_for'   => SRM_OPTION_HIDE_BRANDING,
				'description' => esc_html__( 'Branding on Funnel pages.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_AFFILLIATE_URL, // ID
			esc_html__( 'Branding Content', 'starfish' ), // Title
			array( $this, 'srm_get_textarea_field' ), // Callback
			SRM_OPTION_GROUP_GENERAL, // Page
			'srm_general_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_AFFILLIATE_URL,
				'description' => esc_html__( 'Insert HTML for your branding. Consider joining', 'starfish' ) . ' <a href="https://starfish.reviews/affiliate/" title="Starfish\'s affiliate program" target="_blank">Starfish\'s affiliate program</a> <i class="fas fa-external-link-alt"></i>' . esc_html__( ' and inserting your affiliate link here.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_CLEAN_ON_DEACTIVATE, // ID
			esc_html__( 'Clean On Deactivation', 'starfish' ), // Title
			array( $this, 'srm_premium_get_checkbox_field' ), // Callback
			SRM_OPTION_GROUP_GENERAL, // Page
			'srm_general_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_CLEAN_ON_DEACTIVATE,
				'description' => esc_html__( 'DO NOT use this option, unless you want to remove ALL Starfish Reviews settings and data.', 'starfish' ),
			)
		);

	}

	/*
	 * Defaults Settings Fields
	 */
	function defaults_settings_fields() {

		add_settings_field(
			SRM_OPTION_MSG_NO_THANK_YOU, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Feedback Thank You Message', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_textarea_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_MSG_NO_THANK_YOU . $this->premium_only['container_class'],
				'description' => esc_html__( 'Message displayed after feedback has been submitted on a "No" response.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_NO_FEEDBACK_PROMPT, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Feedback Prompt', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_textarea_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_NO_FEEDBACK_PROMPT . $this->premium_only['container_class'],
				'description' => esc_html__( 'Message displayed after submitting a "No" response.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_REVIEW_PROMPT, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Review Prompt', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_textarea_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_REVIEW_PROMPT . $this->premium_only['container_class'],
				'description' => esc_html__( 'Message displayed after submitting a "Yes" response.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_SUBMIT_BUTTON_NO, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Submit Button Text on "No"', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_SUBMIT_BUTTON_NO . $this->premium_only['container_class'],
				'description' => esc_html__( 'When a user selects "NO" this text will be used on the Submit Button.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_SUBMIT_BUTTON, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Submit Button Text on "Yes"', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_SUBMIT_BUTTON . $this->premium_only['container_class'],
				'description' => esc_html__( 'When a user selects "YES" this text will be used on the Submit Button.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_YN_QUESTION, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Yes/No Question', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_YN_QUESTION . $this->premium_only['container_class'],
				'description' => esc_html__( 'The prompt to recommend this website or not.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_PUBLIC_REVIEW_TEXT, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Public Review Text', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_PUBLIC_REVIEW_TEXT . $this->premium_only['container_class'],
				'description' => esc_html__( 'The text shown above the button(s) to leave a review after skipping negative feedback', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_YES_REDIRECT_SECONDS, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Yes Redirect Seconds', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_YES_REDIRECT_SECONDS . $this->premium_only['container_class'],
				'description' => esc_html__( 'How many seconds to wait before redirecting to the review site.', 'starfish' ),
			)
		);

		add_settings_field(
			SRM_OPTION_DEFAULT_SKIP_FEEDBACK_TEXT, // ID
			$this->premium_only['premium_only_badge'] . esc_html__( 'Skip Feedback Link Text', 'starfish' ) . $this->premium_only['premium_only_link'], // Title
			array( $this, 'srm_premium_get_input_field' ), // Callback
			SRM_OPTION_GROUP_DEFAULTS, // Page
			'srm_defaults_settings_section', // Section ID
			array(
				'label_for'   => SRM_OPTION_DEFAULT_SKIP_FEEDBACK_TEXT . $this->premium_only['container_class'],
				'description' => esc_html__( 'The link text for skipping the feedback form (Requires the Funnel \'Disable Review Gating\' to be disabled).', 'starfish' ),
			)
		);

	}

	/**
	 * SECTION INFO: Print the General Section text
	 */
	function print_general_section_info( $args ) {
		print sprintf( __( 'Specify %1$s settings below', 'starfish' ), $args['title'] );
	}

	/**
	 * SECTION INFO: Print the Email Section text
	 */
	function print_email_section_info( $args ) {
		print sprintf( __( 'Specify %1$s settings below', 'starfish' ), $args['title'] );
	}

	/**
	 * SECTION INFO: Print the Defaults Section text
	 */
	function print_defaults_section_info( $args ) {
		$introduction          = sprintf(
			__( 'Specify the default %1$s settings below. These will apply when creating new <a href="%2$s" title="View Funnels">Funnels</a>.', 'starfish' ),
			$args['title'],
			'/wp-admin/edit.php?post_type=funnel'
		);
		$sidebar_title_toolbox = __( 'Settings Toolbox', 'starfish' );
		$toolbox_button_text   = __( 'Restore System Defaults', 'starfish' );
		$description           = __( 'Replace all existing values with the system defaults.', 'starfish' );
		print <<<D_SECTION
<div class="srm-settings-info">$introduction</div>
<div class="srm-settings-sidebar">
	<div class="srm-settings-sidebar-title"><span class="fas fa-wrench"></span> $sidebar_title_toolbox</div>
	<div id="srm-settings-tool-restore" class="srm-settings-tool">
            <button id="srm-settings-defaults-restore" class="srm-settings-caution-button srm-settings-button">$toolbox_button_text</button>
            <p class="description">$description</p>
    </div>
</div>
D_SECTION;
	}

    /**
     * PREMIUM ONLY
     * CALLBACK: Build premium input field for setting.
     *
     * @param $args
     */
    function srm_premium_get_input_field( $args ) {
        echo sprintf(
            '<div class="%s"><input  name="%s" type="text" id="%s" value="%s"><p %s class="description" id="%s-description">%s</p></div>',
            $this->premium_only['container_class'],
            $args['label_for'] . $this->premium_only['container_class'], // name
            $args['label_for'] . $this->premium_only['container_class'], // id
            esc_html( get_option( $args['label_for'] ) ),
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
	function srm_premium_get_checkbox_field( $args ) {
        echo sprintf(
            '<div class="%s"><input %s name="%s" type="checkbox" id="%s" %s value="yes"><p class="description" id="%s-description">%s</p></div>',
            $this->premium_only['container_class'],
            $this->premium_only['disabled'],
            $args['label_for'] . $this->premium_only['container_class'], // name
            $args['label_for'] . $this->premium_only['container_class'], // id
            ( get_option( $args['label_for'] ) === 'yes' ) ? 'checked="checked"' : '',
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
    function srm_premium_get_textarea_field( $args ) {
        echo sprintf(
            '<textarea name="%s" id="%s" rows="7" cols="60">%s</textarea><p class="description" id="%s-description">%s</p>',
            $args[ 'label_for' ],
            $args[ 'label_for' ],
            get_option( $args[ 'label_for' ] ),
            $args[ 'label_for' ],
            $args[ 'description' ]
        );
    }

    /**
     * CALLBACK: Build textarea field.
     *
     * @param $args
     */
    function srm_get_textarea_field( $args ) {
        echo sprintf(
            '<textarea name="%s" id="%s" rows="7" cols="60">%s</textarea><p class="description" id="%s-description">%s</p>',
            $args[ 'label_for' ],
            $args[ 'label_for' ],
            get_option( $args[ 'label_for' ] ),
            $args[ 'label_for' ],
            $args[ 'description' ]
        );
    }

    /**
     * CALLBACK: Build input field for setting.
     *
     * @param $args
     */
    function srm_get_input_field( $args ) {
        echo sprintf(
            '<input name="%s" type="text" id="%s" value="%s"><p class="description" id="%s-description">%s</p>',
            $args['label_for'], // name
            $args['label_for'], // id
            esc_html( get_option( $args['label_for'] ) ),
            $args['label_for'],
            $args['description']
        );
    }

	/**
	 * Get Setting Options
	 */
	public static function srm_get_options( $type ) {
		switch ( $type ) {
			case 'email':
                $response = array(
					SRM_OPTION_REPLY_TO_EMAIL         => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Use submitter ID as "reply-to" email address.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => 'yes',
						'is_premium'        => true,
					),
					SRM_OPTION_EMAIL_FROM_NAME        => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The name the email will appear to be from.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => '{site-name}',
						'is_premium'        => true,
					),
					SRM_OPTION_EMAIL_FROM_EMAIL       => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Email address the message will appear to be from.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => '{admin-email}',
						'is_premium'        => true,
					),
					SRM_OPTION_EMAIL_SUBJECT          => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Subject of the email.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Feedback from {funnel-name}', 'starfish' ),
						'is_premium'        => false,
					),
					SRM_OPTION_EMAIL_TEMPLATE         => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Contents of the message.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Submitter ID: {tracking-id}<br/>Message: {review-message}<br/>{reviewer-name}<br/>{reviewer-email}<br/>{reviewer-phone}', 'starfish' ),
						'is_premium'        => true,
					),
					SRM_OPTION_DEFAULT_FEEDBACK_EMAIL => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The email address which feedback is sent to.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => '{admin-email}',
						'is_premium'        => false,
					),
				);
				break;
			case 'general':
                $response = array(
                    SRM_OPTION_FUNNEL_ARCHIVES     => array(
                        'type'              => 'boolean',
                        'description'       => esc_html__( 'Disable the Funnel Archive page', 'starfish' ),
                        'sanitize_callback' => null,
                        'show_in_rest'      => true,
                        'default'           => true,
                        'is_premium'        => true,
                    ),
                    SRM_OPTION_FUNNEL_SLUG         => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Change the Funnel Slug', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => 'funnel',
						'is_premium'        => true,
					),
                    SRM_OPTION_HIDE_BRANDING       => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Branding on Funnel pages.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => 'hide',
						'is_premium'        => false,
					),
                    SRM_OPTION_CLEAN_ON_DEACTIVATE => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Delete all settings on deactivation.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => 'no',
						'is_premium'        => false,
					),
                    SRM_OPTION_AFFILLIATE_URL      => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Override "Powered by Starfish" Link', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Powered by <a href="https://starfish.reviews" title="Review Generation on WordPress" target="_blank" rel="nofollow">Starfish Reviews</a>', 'starfish' ),
						'is_premium'        => false,
					),
				);
				break;
			case 'defaults':
                $response = array(
					SRM_OPTION_DEFAULT_SKIP_FEEDBACK_TEXT => array(
						'type'              => 'string',
						'description'       => esc_html__( 'Text for the link to skip the feedback form', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Skip this feedback and leave a public review.', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_skip_feedback_text',
					),
					SRM_OPTION_MSG_NO_THANK_YOU           => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The message shown after submitting Feedback.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Thanks for your feedback. We\'ll review it and work to improve.', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_no_thank_you_msg',
					),
					SRM_OPTION_DEFAULT_NO_FEEDBACK_PROMPT => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The message shown above the Feedback form.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'We\'re sorry we didn\'t meet expectations. How can we do better in the future?', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_no_review_prompt',
					),
					SRM_OPTION_DEFAULT_REVIEW_PROMPT      => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The message shown above the destination options.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( '<h3>Excellent! Please Rate us 5-stars</h3>...and leave a helpful review.', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_yes_review_prompt',
					),
					SRM_OPTION_DEFAULT_SUBMIT_BUTTON_NO   => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The feedback submit button text.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Send Feedback', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_button_text_no',
					),
					SRM_OPTION_DEFAULT_SUBMIT_BUTTON      => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The button text for submitting a review on single destination funnels.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Submit Review', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_button_text',
					),
					SRM_OPTION_DEFAULT_YN_QUESTION        => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The main funnel question text.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Would you recommend {site-name}?', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_yn_question',
					),
					SRM_OPTION_DEFAULT_PUBLIC_REVIEW_TEXT => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The text for leaving a public review after submitting feedback.', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => esc_html__( 'Leave a Public Review', 'starfish' ),
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_public_review_text',
					),
					SRM_OPTION_YES_REDIRECT_SECONDS       => array(
						'type'              => 'string',
						'description'       => esc_html__( 'The number of seconds to wait before auto-redirecting to the single destination (0 = disabled; "imm" = immediately).', 'starfish' ),
						'sanitize_callback' => null,
						'show_in_rest'      => true,
						'default'           => '0',
						'is_premium'        => true,
						'funnel_cf_id'      => '_srm_review_auto_redirect',
					),
				);
				break;
            default:
                $response = null;
		}
		return $response;
	}

}

if ( is_admin() && class_exists( 'SRM_SETTINGS', true ) ) {
    return new SRM_SETTINGS();
}
