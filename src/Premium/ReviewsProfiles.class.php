<?php

namespace Starfish\Premium;

use Starfish\Utils as SRM_UTILS;
use Starfish\Premium\Cron as SRM_CRON_JOBS;
use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;
use Starfish\Premium\ReviewsRs as SRM_REVIEWS_RS;
use WP_List_Table;
use function _n;
use function array_slice;
use function date;
use function sprintf;
use function strtotime;
use const SRM_PLANS_URL;

/**
 * Starfish Reviews Profiles extension of the WordPress WP_List_Table API
 *
 * For interacting with and displaying Reviews captured from the external Reviews API
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
 * @since      3.0.0
 */
class ReviewsProfiles extends WP_List_Table {

	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Profile', 'starfish' ),
				'plural'   => __( 'Profiles', 'starfish' ),
				'ajax'     => true,
			)
		);
	}

	/**
	 * Retrieve Profile Details. Requires a profile to be added first
	 *
	 * @param String $job_id Profile Job ID
	 *
	 * @return Array $profile Profile Details
	 * @see    self::add_profile()
	 */
	public static function get_profile_by_job( $job_id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}srm_view_reviews_profiles WHERE 'job_id' = %d", $job_id );

		return $wpdb->get_results( $sql, 'ARRAY_A' ); // db call ok; no-cache ok.
	}

	/**
	 * Create Review Profile
	 *
	 * @param array $data Profile Data.
	 *
	 * @return int|string|false profile ID, otherwise false
	 */
	public static function add_profile( $data = array() ) {
		global $wpdb;

		$profiles = SRM_UTILS_REVIEWS::get_profiles();

		$result = $wpdb->insert( $wpdb->prefix . 'srm_reviews_profiles', $data ); // db call ok; no-cache ok.

		// Check if no profiles existed before this one.
		if ( empty( $profiles ) ) {
			// Then schedule the expiration validation cron-job.
			wp_schedule_event( time(), SRM_CRON_JOBS::CRON_SCHEDULES['DAILY'], SRM_CRON_JOBS::CRON_EXPIRE_PROFILES );
		}

		if ( $result !== false ) {
			$profile_id = $wpdb->insert_id;
		} else {
			$profile_id = false;
		}

		return $profile_id;
	}

	/**
	 * Retrieve profile's data from the database
	 *
	 * @param int $per_page Number per page
	 * @param int $page_number Page number
	 *
	 * @return mixed $profiles
	 */
	public static function get_reviews_profiles( $per_page = 5, $page_number = 1 ) {
		global $wpdb;
		$schema_check = "SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = '{$wpdb->dbname}' AND table_name = '{$wpdb->prefix}srm_view_reviews_profiles'";
		$table_exists = $wpdb->get_results( $schema_check, ARRAY_A ); // db call ok; no-cache ok.
		$results      = null;
		if ( $table_exists[0]['count'] === '1' ) {
			$sql = "SELECT * FROM {$wpdb->prefix}srm_view_reviews_profiles";
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
			}
			$sql    .= " LIMIT $per_page";
			$sql    .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
			$results = $wpdb->get_results( $sql, 'ARRAY_A' ); // db call ok; no-cache ok.
		}

		return $results;
	}

	/**
	 * Update Review Profile
	 *
	 * @param array $profile_data Profile Data.
	 * @param int $profile_id Profile ID.
	 *
	 * @return int|false profile ID, otherwise false
	 */
	public static function update_profile( $profile_id, $profile_data ) {
		global $wpdb;
		// Unset summary data due to the profile data being retrieved from the profile_summary view
		unset( $profile_data['review_count'] );
		unset( $profile_data['average_rating'] );
		unset( $profile_data['last_crawl'] );
		unset( $profile_data['crawl_status'] );
		unset( $profile_data['percentage_complete'] );
		unset( $profile_data['result_count'] );
		unset( $profile_data['summary_updated'] );
		unset( $profile_data['created'] );
		$result = $wpdb->update( $wpdb->prefix . 'srm_reviews_profiles', $profile_data, array( 'id' => $profile_id ) ); // db call ok;no-cache ok.
        if ( $result === false ) {
			$profile_id = false;
		}

		return $profile_id;
	}

	/**
	 * Text displayed when no reviews profiles data is available
	 *
	 * @return null
	 */
	public function no_items() {
		_e( 'No review profiles available.', 'starfish' );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public static function column_name( $item ) {

		// create a nonce
		$delete_nonce = wp_create_nonce( 'delete_reviews_profile' );

		// default site logo
		$reviews_site_logo = SRM_PLUGIN_URL . '/assets/logos/default.png';
		$site_name         = null;
		$profile           = SRM_UTILS_REVIEWS::get_profile( $item['id'] );
		$profile_host      = wp_parse_url( $profile->url, PHP_URL_HOST );
		foreach ( SRM_UTILS_REVIEWS::get_review_sites() as $site ) {
			$site_host = wp_parse_url( $site->url, PHP_URL_HOST );
			if ( $site_host === $profile_host ) {
				$reviews_site_logo = $site->logo;
				$site_name         = $site->name;
				break;
			}
		}
		$icon = '<div class="srm-reviews-profile-icon"><img alt="Site Logo" src="' . $reviews_site_logo . '"/></div>';

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = array(
			// Delete the profile
			'delete'  => sprintf(
				'<a href="?post_type=starfish_feedback&page=%s&action=%s&srm_reviews_profiles=%s&_wpnonce=%s">Delete</a>',
				esc_attr( $_REQUEST['page'] ),
				'delete',
				absint( $item['id'] ),
				$delete_nonce
			),
			// Open the profile details including last crawl stats and options to hide/activate
			'details' => sprintf(
				'<a href="?post_type=starfish_feedback&page=%s&action=%s&id=%s&site=%s">Details</a>',
				esc_attr( $_REQUEST['page'] ),
				'details',
				absint( $item['id'] ),
				$site_name
			),
		);

		return $icon . $title . ( new ReviewsProfiles )->row_actions( $actions );
	}

	/**
	 * Method for visibility column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public static function column_hide( $item ) {
		$profile = SRM_UTILS_REVIEWS::get_profile( $item['id'] );
		$checked = '';
		if ( $profile->hide ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		return '<input type="checkbox" ' . $checked . ' data-srm_reviews_profile_id="' . $item['id'] . '" data-style="srm-reviews-profiles-hide-toggle" data-size="small" data-toggle="toggle" data-on="Hidden" data-off="Visible" data-onstyle="warning" data-offstyle="info">';
	}

	/**
	 * Method for active column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public static function column_active( $item ) {
		$profile = SRM_UTILS_REVIEWS::get_profile( $item['id'] );
		$status  = $profile->active;
		$updated = $profile->summary_updated;

		if ( $status == 1 ) {
			$status = SRM_UTILS_REVIEWS::ACTIVE;
		} elseif ( $status == 3 ) {
			$status = SRM_UTILS_REVIEWS::EXPIRED;
		} else {
			$status = SRM_UTILS_REVIEWS::DISABLED;
		}

		$value = '<div id="srm-reviews-profile-' . $item['id'] . '" class="srm-reviews-profiles-status ' . strtolower( $status ) . '">' . $status . ' </div>';

		$actions = array();
		if ( strtolower( $status ) === 'expired' ) {
			$actions['renew'] = sprintf(
				'<a class="srm-reviews-profile-renew" href="javascript:void(0);" data-srm_profile_id="%s">Renew Profile</a>',
				absint( $item['id'] )
			);
		}

		return $value . ( new ReviewsProfiles )->row_actions( $actions );
	}

	/**
	 * Method for Last Scrape Status column
	 * <pre>
	 * pending      The job is still in the queue and is pending completion
	 * complete     The job is completed
	 * maintenance  There has been an issue with the scrape - our team is automatically notified for investigation
	 * invalid_url  The URL you have provided is invalid
	 * </pre>
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public static function column_crawl_status( $item ) {
		$profile = SRM_UTILS_REVIEWS::get_profile( $item['id'] );

		switch ( $profile->crawl_status ) {
			case null:
				$value = 'Not Available';
				break;
			case 'pending':
				$value = 'Processing';
				break;
			case 'complete':
				$value = 'Completed';
				break;
			case 'maintenance':
				$value = 'In Maintenance';
				break;
			case 'invalid_url':
				$value = 'Invalid URL';
				break;
            case 'failed':
                $value = 'Failed';
                break;
			default:
				$value = 'Unknown';
		}

		return $value;
	}

	/**
	 * Method for Last Scrape Date column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public static function column_last_crawl( $item ) {
		$profile    = SRM_UTILS_REVIEWS::get_profile( $item['id'] );
		$last_crawl = $profile->last_crawl;
		if ( empty( $last_crawl ) ) {
			$value = 'Not Available';
		} else {
			$value = date( 'm/d/Y', strtotime( $last_crawl ) );
		}

		return $value;
	}

    /**
     * Method for total review count imported
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public static function column_review_count( $item ) {
        $profile = SRM_UTILS_REVIEWS::get_profile( $item['id'] );
        $total_reviews = $profile->review_count;
        if ( empty( $total_reviews ) ) {
            $value = 'Not Available';
        } else {
            $value = $total_reviews;
        }
        return $value;
    }

    /**
     * Method for the average rating of imported reviews
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public static function column_average_rating( $item ) {
        $profile        = SRM_UTILS_REVIEWS::get_profile( $item['id'] );
        $average_rating = $profile->average_rating;
        if ( empty( $average_rating ) ) {
            $value = 'Not Available';
        } else {
            $value = '<div class="srm-reviews-rating" title="' . $average_rating . ' Star Rating">';
            for ( $s = 1; $s <= $average_rating; $s ++ ) {
                $value .= '<span class="fa fa-star srm-reviews-star"></span>';
            }
        }
        return $value . '</div>';
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
			case 'name':
			case 'hide':
			case 'active':
			case 'last_crawl':
			case 'crawl_status':
            case 'review_count':
            case 'average_rating':
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
	 *  Associative array of Review Profile columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'name'           => __( 'Name', 'starfish' ),
			'hide'           => __( 'Visibility', 'starfish' ),
			'active'         => __( 'Status', 'starfish' ),
			'crawl_status'   => __( 'Scrape Status', 'starfish' ),
			'last_crawl'     => __( 'Last Scrape', 'starfish' ),
            'review_count'   => __( 'Review Count', 'starfish' ),
            'average_rating' => __( 'Average Rating', 'starfish' ),
		);
	}

	/**
	 * Review Profile Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'name'           => array( 'name', true ),
			'hide'           => array( 'hide', true ),
			'active'         => array( 'active', true ),
			'last_crawl'     => array( 'last_crawl', true ),
			'crawl_status'   => array( 'crawl_status', true ),
            'review_count'   => array( 'review_count', true ),
            'average_rating' => array( 'average_rating', true ),
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
		$this->_column_headers = $this->get_column_info();

		/**
		 * Process bulk action
		 */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'reviews_profiles_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = self::get_reviews_profiles( $per_page, $current_page );
	}

	public function process_bulk_action() {

		// Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'delete_reviews_profile' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				self::delete_reviews_profile( $_GET['srm_reviews_profiles'] );
			}
		}
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] === 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] === 'bulk-delete' ) ) {
			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_reviews_profile( $id );
			}
		}
	}

	/**
	 * Delete a reviews profile record.
	 *
	 * @param int $id reviews profile ID
	 *
	 * @return int|false The number of profiles updated, or false on error.
	 */
	public static function delete_reviews_profile( $id ) {
		global $wpdb;
		return $wpdb->delete(
			"{$wpdb->prefix}srm_reviews_profiles",
			array( 'id' => $id )
		); // db call ok; no-cache ok.

	}

	/**
	 * Returns the count of reviews profiles in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}srm_reviews_profiles";

		return $wpdb->get_var( $sql ); // db call ok; no-cache ok.
	}

	/**
	 * Update a given profile's hidden setting
	 *
	 * @param string $profile_id The profile ID
	 * @param boolean $is_hidden The value to update the hidden flag to
	 *
	 * @return false|int
	 */
	public static function update_profile_hide( $profile_id, $is_hidden ) {
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

		return $wpdb->update( $wpdb->prefix . 'srm_reviews_profiles', array( 'hide' => $hide ), array( 'id' => $profile_id ) ); // db call ok; no-cache ok.
	}

	/**
	 * Update a given profile's scrape status
	 *
	 * @param string $profile_id The profile ID
	 * @param string $status The status
	 *
	 * @return false|int
	 * @see scrape_reviews_callback
	 */
	public static function update_scrape_status( $profile_id, $status ) {
		global $wpdb;
		$result = $wpdb->update( $wpdb->prefix . 'srm_reviews_last_summary', array( 'crawl_status' => '' . $status . '' ), array( 'profile_id' => $profile_id ) ); // db call ok; no-cache ok.

		return $result;
	}

	/**
	 * Scrape for new reviews for the given profile ID
	 * Update the Last Summary record for the profile
	 *
	 * @param string $profile_id The Profile ID for the requested reviews
	 * @param string $job_id The job ID assigned to the profile
	 *
	 * @return int|false
	 */
	public static function scrape_reviews( $profile_id, $job_id ) {
		$summary_id = false;
		$profile    = SRM_UTILS_REVIEWS::get_profile( $profile_id );

		// Check if profile is expired
		if ( strtolower( $profile->status ) === SRM_UTILS_REVIEWS::ACTIVE ) {
			$rs_reviews_response = SRM_REVIEWS_RS::get_reviews( $job_id );
			// Insert the reviews and update the last summary.
			if ( $rs_reviews_response->success ) {
				foreach ( $rs_reviews_response->reviews as $review ) {
                    SRM_UTILS_REVIEWS::add_review( $profile_id, $review );
				}
				$summary_id = true;
			}
		}

		return $summary_id;
	}

	/**
	 * Check if profile is expired; if so change status
	 *
     * @param string $profile_id
	 * @param string $summary_updated
	 *
	 * @return boolean
	 */
	public static function is_expired( $profile_id, $summary_updated ) {

		if ( strtotime( $summary_updated ) <= strtotime( '-30 days' ) ) {
            SRM_UTILS_REVIEWS::update_profile_status( $profile_id, SRM_UTILS_REVIEWS::EXPIRED );

			return true;
		}

		return false;
	}

	/**
	 * Retrieve Profile Details. Requires a profile to be added first
	 *
	 * @param String $profile_id Profile ID
	 *
	 * @return Object $profile Profile Details
	 * @see    self::add_profile()
	 */
	public static function get_profile( $profile_id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}srm_view_reviews_profiles WHERE id = %d", $profile_id );

		$result = $wpdb->get_results( $sql, 'OBJECT_K' ); // db call ok; no-cache ok.

		return $result[ $profile_id ];
	}

    /**
     * Set all extra profiles past plan limit to Disabled and Hidden (by created date descending)
     *
     * @param integer $limit
     *
     * @return integer number of profiles in violation of plan limits
     */
    public static function srm_force_extra_profiles_to_disabled( $limit ) {

        $active_profiles = SRM_UTILS_REVIEWS::get_profiles(true);
        usort( $active_profiles, function($a, $b) {return(SRM_UTILS::date_compare($a, $b));} );
        $count = count( $active_profiles );
        if(empty($limit)) {
            $violations = 0; // Account for unlimited plans
        } else {
            $violations = $count - $limit;
        }
        if($violations > 0 ) {
            $to_disable = array_slice($active_profiles, -$violations);
            foreach($to_disable as $profile) {
                self::update_profile_hide( $profile[ 'id' ], true );
                SRM_UTILS_REVIEWS::update_profile_status( $profile[ 'id' ], SRM_UTILS_REVIEWS::DISABLED );
            }
        }

        return $violations;
    }

}