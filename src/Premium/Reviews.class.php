<?php

namespace Starfish\Premium;

use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;
use WP_List_Table;
use const SRM_UPLOADS_DIR;

/**
 * Starfish Reviews extension of the WordPress WP_List_Table API
 *
 * For interacting with and displaying Reviews Profiles captured from the external Reviews API
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    1.0.0
 * @link       https://api.supervisor.reviewshake.com
 * @see        ReviewsRs
 * @since      Release: 3.0.0
 */
class Reviews extends WP_List_Table {

	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Review', 'starfish' ),
				'plural'   => __( 'Reviews', 'starfish' ),
				'ajax'     => true,
			)
		);
	}

	/**
	 * Text displayed when no reviews data is available
	 *
	 * @return null
	 */
	public function no_items() {
		_e( 'No reviews available.', 'starfish' );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		// create a nonce
		$delete_nonce = wp_create_nonce( 'delete_srm_review' );
		$review          = $this->get_review( $item['id'] );
		$profile_picture = SRM_PLUGIN_URL . '/assets/default_profile_image.png';
		$avatar          = 'srm_review_avatar_' . $review->uuid . '.png';
		$avatar_path     = SRM_UPLOADS_DIR . $avatar;
        $avatar_url      = SRM_UPLOADS_URL . $avatar;
		if( file_exists( $avatar_path ) ) {
			$profile_picture = $avatar_url;
		}
		$icon = '<div class="srm-reviews-icon"><img title="' . $item['name'] . '" alt="Reviewer Profile" src="' . $profile_picture . '"/></div>';

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = array(
			'delete'  => sprintf(
				'<a href="?post_type=starfish_feedback&page=%s&action=%s&srm_review=%s&_wpnonce=%s">Delete</a>',
				esc_attr( $_REQUEST['page'] ),
				'delete',
				absint( $item['id'] ),
				$delete_nonce
			),
			'details' => sprintf(
				'<a href="?post_type=starfish_feedback&page=%s&action=%s&srm_review=%s">Details</a>',
				esc_attr( $_REQUEST['page'] ),
				'details',
				absint( $item['id'] )
			),
		);

		return $icon . $title . $this->row_actions( $actions );
	}

	/**
	 * Retrieve Review.
	 *
	 * @param String $review_id Review ID
	 *
	 * @return Object $review Review Details
	 */
	public static function get_review( $review_id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}srm_reviews WHERE id = %d", $review_id );

		$result = $wpdb->get_results( $sql, 'OBJECT_K' ); // db call ok; no-cache ok.

		return $result[ $review_id ];
	}

	/**
	 * Method for date column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_date( $item ) {
		return gmdate( 'm/d/Y', strtotime( $item['date'] ) );
	}

	/**
	 * Method for rating_value column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_rating_value( $item ) {
		$value = '<div class="srm-reviews-rating" title="' . $item['rating_value'] . ' Star Rating">';
		for ( $s = 1; $s <= $item['rating_value']; $s ++ ) {
			$value .= '<span class="fa fa-star srm-reviews-star"></span>';
		}

		return $value . '</div>';
	}

	/**
	 * Method for rating URL column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_url( $item ) {
		if ( empty( $item['url'] ) ) {
			$url = 'Not Available';
		} else {
			$url = $item['url'];
		}
		if ( empty( $item['review_title'] ) ) {
			$title = 'View Review';
		} else {
			$title = $item['review_title'];
		}

		return sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', $url, $title, $title ) . ' <span class="fas fa-external-link-alt"></span>';
	}

	/**
	 * Method for rating profile column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_profile_id( $item ) {
		$profile      = SRM_REVIEWS_PROFILES::get_profile( $item['profile_id'] );
		$profile_host = wp_parse_url( $profile->url, PHP_URL_HOST );
		$site_name    = null;
		foreach ( SRM_UTILS_REVIEWS::get_review_sites() as $site ) {
			$site_host = wp_parse_url( $site->url, PHP_URL_HOST );
			if ( $site_host === $profile_host ) {
				$review_site_logo = $site->logo;
				$site_name        = $site->name;
				break;
			}
		}

		return '<div class="srm-reviews-profile-icon"><a href="/wp-admin/edit.php?post_type=starfish_feedback&page=starfish-reviews-profiles&action=details&id=' . $item['profile_id'] . '&site=' .
			   $site_name . '" title="Click to View Profile"><img alt="' . $site_name . '" src="' . $review_site_logo . '"/> ' . $profile->name . '</a></div>';
	}

	/**
	 * Method for rating location column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_location( $item ) {
		if ( empty( $item['location'] ) ) {
			$value = 'Not Available';
		} else {
			$value = $item['location'];
		}

		return $value;
	}

	/**
	 * Method for visibility column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_hide( $item ) {
		$review  = $this->get_review( $item['id'] );
		$checked = '';
		if ( $review->hide ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		return '<input type="checkbox" ' . $checked . ' data-srm_review_id="' . $item['id'] . '" data-style="srm-review-hide-toggle" data-size="small" data-toggle="toggle" data-on="Hidden" data-off="Visible" data-onstyle="warning" data-offstyle="info">';
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item Review record
	 * @param string $column_name Review column name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'meta_data':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 *  Associative array of Review columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'name'         => __( 'Name', 'starfish' ),
			'hide'         => __( 'Visibility', 'starfish' ),
			'date'         => __( 'Date', 'starfish' ),
			'rating_value' => __( 'Rating', 'starfish' ),
			'location'     => __( 'Location', 'starfish' ),
			'url'          => __( 'Review', 'starfish' ),
			'profile_id'   => __( 'Profile', 'starfish' ),
		);
	}

	/**
	 * Review Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'name'         => array( 'name', true ),
			'hide'         => array( 'hide', true ),
			'date'         => array( 'date', true ),
			'rating_value' => array( 'rating_value', true ),
			'location'     => array( 'location', true ),
			'profile_id'   => array( 'profile_id', true ),
		);
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk-delete' => 'Delete',
		);
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->get_column_info();

		/**
		 * Process bulk action
		 */
		$this->process_bulk_action();
		$per_page     = $this->get_items_per_page( 'reviews_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
		$this->items = self::get_reviews( $per_page, $current_page );
	}

	public function process_bulk_action() {
		// Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'delete_srm_review' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$this->delete_review( absint( $_GET['srm_review'] ) );
				wp_safe_redirect( esc_url( $_SERVER['REQUEST_URI'] ) );
				exit;
			}
		}
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] === 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] === 'bulk-delete' )
		) {
			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				$this->delete_review( $id );
			}
			wp_safe_redirect( esc_url( $_SERVER['REQUEST_URI'] ) );
			exit;
		}
	}

	/**
	 * Delete a review record.
	 *
	 * @param int $id review ID
	 *
	 * @return null|string
	 */
	public function delete_review( $id ) {
		global $wpdb;
		$review      = $this->get_review( $id );
		$review_uuid = $review->uuid;
		$result      = $wpdb->delete(
			"{$wpdb->prefix}srm_reviews",
			array( 'ID' => $id ),
			array( '%d' )
		); // db call ok; no-cache ok.
		// Delete the review's avatar, if present
		$avatar_file = SRM_UPLOADS_DIR . 'srm_review_avatar_' . $review_uuid . '.png';
		if( file_exists( $avatar_file ) ) {
			unlink( $avatar_file );
		}

		return $result;
	}

	/**
	 * Returns the count of reviews in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}srm_reviews";

		return $wpdb->get_var( $sql ); // db call ok; no-cache ok.
	}

	/**
	 * Retrieve review’s data from the database
	 *
	 * @param int $per_page Number per page
	 * @param int $page_number Page number
	 *
	 * @return mixed $reviews
	 */
	public function get_reviews( $per_page = 5, $page_number = 1 ) {

		global $wpdb;
		$schema_check = "SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = '{$wpdb->dbname}' AND table_name = '{$wpdb->prefix}srm_reviews'";
		$table_exists = $wpdb->get_results( $schema_check, ARRAY_A ); // db call ok; no-cache ok.
		$results      = null;
		if ( $table_exists[0]['count'] === '1' ) {
			$sql = "SELECT * FROM {$wpdb->prefix}srm_reviews";
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
			}
			$sql     .= " LIMIT $per_page";
			$sql     .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
			$results = $wpdb->get_results( $sql, 'ARRAY_A' ); // db call ok; no-cache ok.
		}

		return $results;

	}

	/**
	 * Update a given review's hidden setting
	 *
	 * @param string $review_id The review ID
	 * @param boolean $is_hidden The value to update the hidden flag to
	 *
	 * @return false|int
	 */
	public function update_review_hide( $review_id, $is_hidden ) {
		global $wpdb;
		switch ( $is_hidden ) {
			case true:
				$hide = '1';
				break;
			case false:
				$hide = '0';
				break;
			default:
				$hide = '0';
		}

		return $wpdb->update( $wpdb->prefix . 'srm_reviews', array( 'hide' => $hide ), array( 'id' => $review_id ) );
	}

}