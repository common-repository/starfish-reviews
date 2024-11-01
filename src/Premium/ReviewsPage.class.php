<?php

namespace Starfish\Premium;

use Starfish\Premium\Reviews as SRM_REVIEWS;

class ReviewsPage {

	// class instance
	static $instance;

	// Reviews WP_List_Table object
	public $reviews;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'reviews_submenu' ) );
	}

	/**
	 * Singleton instance
	 *
	 * @return ReviewsPage
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
	public function reviews_submenu() {
		$hook = add_submenu_page(
			'edit.php?post_type=starfish_feedback', // Parent Slug
			esc_html__( 'Starfish Reviews', 'starfish' ), // Page Title
			esc_html__( 'Reviews', 'starfish' ), // Menu Title
			'administrator', // Capability required
			'starfish-reviews', // Menu Slug
			array( $this, 'admin_reviews_page' ) // Content Call Back
		);
		add_action( "load-$hook", array( $this, 'screen_option' ) );
	}

	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => 'Reviews',
			'default' => 25,
			'option'  => 'reviews_per_page',
		);
		add_screen_option( $option, $args );
		$this->reviews = new SRM_REVIEWS;
	}

	/**
	 * Add Reviews Page (callback)
	 */
	public function admin_reviews_page() {

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Reviews</h1>
			<hr class="wp-header-end"/>
			<div id="srm-reviews-admin">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->reviews->prepare_items();
								$this->reviews->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
		include SRM_DIALOGUE_REVIEW_DETAILS;
	}

}