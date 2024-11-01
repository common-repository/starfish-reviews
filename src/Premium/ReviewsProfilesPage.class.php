<?php

namespace Starfish\Premium;

use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;
use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use const SRM_PLAN_LIMIT;

class ReviewsProfilesPage {

	// class instance
	public static $instance;

	// Profiles WP_List_Table object
	public $reviews_profiles;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'reviews_profiles_submenu' ) );
	}

	/**
	 * Singleton instance
	 *
	 * @return SRM_REVIEWS_PAGE
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Add Settings Sub-menu page
	 */
	public function reviews_profiles_submenu() {
		$hook = add_submenu_page(
			'edit.php?post_type=starfish_feedback', // Parent Slug
			esc_html__( 'Starfish Reviews Profiles', 'starfish' ), // Page Title
			esc_html__( 'Profiles', 'starfish' ), // Menu Title
			'administrator', // Capability required
			'starfish-reviews-profiles', // Menu Slug
			array( $this, 'admin_reviews_profiles_page' ) // Content Call Back
		);

		add_action( "load-$hook", array( $this, 'screen_option' ) );
	}

	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => 'Review Profiles',
			'default' => 25,
			'option'  => 'reviews_profiles_per_page',
		);
		add_screen_option( $option, $args );
		$this->reviews_profiles = new SRM_REVIEWS_PROFILES;
	}

	/**
	 * Add Reviews Page (callback)
	 */
	public function admin_reviews_profiles_page() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Review Profiles</h1>
            <?php if ( ( count( SRM_UTILS_REVIEWS::get_profiles(true) ) < SRM_PLAN_LIMIT['profiles'] ) || empty(SRM_PLAN_LIMIT['profiles']) ) { ?>
                <a id="srm-reviews-profiles-add-new" class="page-title-action">Add New</a>
            <?php } ?>
			<hr class="wp-header-end"/>
			<div id="srm-reviews-profiles-admin">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->reviews_profiles->prepare_items();
								$this->reviews_profiles->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
		include SRM_DIALOGUE_REVIEWS_PROFILES;
	}
}