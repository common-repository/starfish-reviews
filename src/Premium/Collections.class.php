<?php

namespace Starfish\Premium;

use WP_Query;
use const PHP_URL_HOST;
use const SRM_PLAN_LIMIT;
use const SRM_PLUGIN_URL;
use const SRM_UPLOADS_DIR;
use Starfish\Logging as SRM_LOGGING;

/**
 * Collection class for dealing with collections
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0.0
 * @link       https://api.supervisor.reviewshake.com
 * @see        ReviewsRs, ReviewsProfiles, Reviews
 * @since      Release: 2.4.0
 */
class Collections {

    /**
     * The Freemius plan
     *
     * @return bool
     */
    public static function srm_restrict_add_new() {
        $total_collections = self::srm_get_total_collections();
        $restricted    = true;
        if ( $total_collections <= SRM_PLAN_LIMIT['collections'] || null === SRM_PLAN_LIMIT['collections'] ) {
            $restricted = false;
        }

        return $restricted;
    }

	/**
	 * Get a collection by ID
	 *
	 * @param $id string Collection ID
	 *
	 * @return object $collection The collection
	 */
	static function get_collection( $collection_id ) {

		$args = array(
			'numberposts'      => 1,
			'category'         => 0,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => array( $collection_id ),
			'exclude'          => array(),
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'collection',
			'suppress_filters' => true,
		);

		$posts = get_posts( $args );

		return $posts[0];

	}

	/**
	 * @param string $collection_id The collection ID
	 *
	 * @param boolean $hidden whether to hide the collection or not
	 *
	 * @return boolean True or false whether the update was successful
	 */
	static function update_collection_hide( $collection_id, $hidden ) {

		return update_post_meta( $collection_id, '_srm_collection_hidden', $hidden );

	}

	/**
	 * Get reviews according to collection arguments
	 *
	 * @param array $args Arguments from collection settings.
	 *
	 * @return array $reviews The resulting reviews
	 */
	public static function get_collection_reviews( $args ) {

		global $wpdb;
		$profile_ids = $args['profile_ids'];
		$min_rating  = $args['min_rating'];
		$max_reviews = $args['max_reviews'];
		$start_date  = $args['start_date'];
		$end_date    = $args['end_date'];
		$no_review   = $args['no_review'];

		// Determine if profiles are visible or not
		if(!empty($profile_ids)) {
			foreach ( $profile_ids as $key => $id ) {
				$profile = UtilsReviews::get_profile( $id );
				if ( $profile->hide ) {
					unset( $profile_ids[ $key ] );
				}
			}
			$profiles = implode( ',', $profile_ids );
			// Build query based on arguments.
			if ( is_string( $min_rating ) ) {
				$min_rating = "'" . $min_rating . "'";
			}
			$query = "SELECT * FROM {$wpdb->prefix}srm_reviews WHERE profile_id in ({$profiles}) AND rating_value >= {$min_rating} AND hide <> true";
			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
				$query .= " AND date BETWEEN CAST({$start_date} AS DATE) AND CAST({$end_date} AS DATE)";
			}
			if ( ! $no_review ) {
				$query .= ' AND review_text IS NOT NULL';
			}
			if ( $max_reviews > 0 ) {
				$query .= ' LIMIT ' . $max_reviews;
			}

			return $wpdb->get_results( $query, 'ARRAY_A' ); // db call ok; no-cache ok.
		} else {
			return [];
		}

	}

	/**
	 * Get invalid collection notice.
	 *
	 * @param integer $collection_id The Collection ID.
	 *
	 * @return string $notice
	 */
	public static function srm_invalid_collection_notice( $collection_id ) {
		$notice = '<div class="srm-notice-message">';
		$notice .= '<i class="fas fa-exclamation-circle fa-2x"> ' . __( 'Starfish\Premium Notice', 'starfish' ) . '</i><br/>';
		$notice .= sprintf( __( 'This Collection (%1$s) is not valid or published yet!', 'starfish' ), $collection_id );
		$notice .= '</div>';

		return $notice;
	}

    /**
     * Enqueue the Collection associated scripts.
     *
     * @param $post_id
     *
     * @return null
     */
	public static function srm_enqueue_collection_scripts( $collection_id ) {
		$version = SRM_VERSION . '+' . wp_rand( 1, 99999999999 );
		wp_enqueue_style( 'srm_bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css', false, '4.4.1', 'all' );
		wp_enqueue_style( 'srm_collection', SRM_PLUGIN_URL . '/css/starfish-collection.css', false, $version, 'all' );
		wp_enqueue_script( 'srm_bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js', false, '4.4.1', false );
	}

	/**
	 * Get the collection variables for building the Collection template
	 *
	 * @param integer $collection_id The collection ID.
	 *
	 * @return array
	 */
	public static function srm_get_collection_variables( $collection_id ) {

		$slider          = get_post_meta( $collection_id, '_srm_collection_slider', true );
		$columns         = get_post_meta( $collection_id, '_srm_collection_columns', true );
		$rows            = get_post_meta( $collection_id, '_srm_collection_rows', true );
		$visible_reviews = intval( $columns ) * intval( $rows ); // The number of visible reviews (per slide if slider is enabled).
		$max_reviews     = intval( get_post_meta( $collection_id, '_srm_collection_filters_maxreviews', true ) );

		if ( ! $slider ) {
			$max_reviews = $visible_reviews;
		}

		$args = array(
			'profile_ids' => get_post_meta( $collection_id, '_srm_collection_filters_profiles', true ),
			'min_rating'  => get_post_meta( $collection_id, '_srm_collection_filters_minrating', true ),
			'no_review'   => get_post_meta( $collection_id, '_srm_collection_elements_noreview', true ),
			'max_reviews' => $max_reviews,
			'start_date'  => null,
			'end_date'    => null,
		);
		if ( 'range' === get_post_meta( $collection_id, '_srm_collection_filters_age', true ) ) {
			$args['start_date'] = get_post_meta( $collection_id, '_srm_collection_filters_startdate', true );
			$args['end_date']   = get_post_meta( $collection_id, '_srm_collection_filters_enddate', true );
		}

		$reviews = Collections::get_collection_reviews( $args );
		foreach($reviews as $key => $review) {
			$avatar      = 'srm_review_avatar_' . $review['uuid'] . '.png';
			$avatar_path = SRM_UPLOADS_DIR . $avatar;
			$avatar_url  = SRM_UPLOADS_URL . $avatar;
            if(file_exists($avatar_path)) {
                $review['avatar_url'] = $avatar_url;
            } else {
                $review['avatar_url'] = SRM_PLUGIN_URL . '/assets/default_profile_image.png';
            }
            $reviews[ $key ] = $review;
            $review_host     = str_replace('www.', '', wp_parse_url( $review['url'], PHP_URL_HOST ));
			foreach ( UtilsReviews::get_review_sites() as $site ) {
				$site_host = wp_parse_url( $site->url, PHP_URL_HOST );
				if ( strpos( $site_host, $review_host ) !== false ) {
					$review['source_logo'] = $site->logo;
					$review['source_name'] = $site->name;
					$reviews[ $key ]       = $review;
					break;
				}
			}
		}
		$total_reviews = count($reviews);
		// Calculate and build the reviews by columns and rows.
		$slide_count = null;
		$slides      = null;
		if ( $visible_reviews > 0 ) {
			$slide_count = ceil( $total_reviews / $visible_reviews );
		}
		if ( ! empty ( $slide_count ) ) {
			for ( $s = 1; $s <= $slide_count; $s ++ ) {
				$review_rows = null;
				for ( $r = 1; $r <= $rows; $r ++ ) {
					$review_rows[] = array_slice( $reviews, 0, $columns );
					array_splice( $reviews, 0, $columns );
				}
				$slides[] = $review_rows;
			}
		}

		// Template Variables.
		return array(
			'slide_count'   => $slide_count,
			'collection_id' => $collection_id,
			'shortcode'     => false,
			'slides'        => $slides,
			'total_reviews' => $total_reviews,
			'hide_branding' => get_option( SRM_OPTION_HIDE_BRANDING, true ),
			'affiliate_url' => get_option( SRM_OPTION_AFFILIATE_URL, true ),
			'layout'        => get_post_meta( $collection_id, '_srm_collection_layout', true ),
			'slider'        => $slider,
			'autoadvance'   => get_post_meta( $collection_id, '_srm_collection_autoadvance', true ),
			'navigation'    => get_post_meta( $collection_id, '_srm_collection_navigation', true ),
			'elements'      => array(
				'title'    => get_post_meta( $collection_id, '_srm_collection_elements_title', true ),
				'rating'   => get_post_meta( $collection_id, '_srm_collection_elements_rating', true ),
				'date'     => get_post_meta( $collection_id, '_srm_collection_elements_date', true ),
				'avatar'   => get_post_meta( $collection_id, '_srm_collection_elements_avatar', true ),
				'name'     => get_post_meta( $collection_id, '_srm_collection_elements_name', true ),
				'readmore' => get_post_meta( $collection_id, '_srm_collection_elements_readmore', true ),
				'source'   => get_post_meta( $collection_id, '_srm_collection_elements_source', true ),
				'review'   => get_post_meta( $collection_id, '_srm_collection_elements_review', true ),
			),
			'maxchar'       => intval( get_post_meta( $collection_id, '_srm_collection_elements_maxchar', true ) ),
			'name_just'     => get_post_meta( $collection_id, '_srm_collection_name_justification', true ),
			'name_font'     => get_post_meta( $collection_id, '_srm_collection_name_font', true ),
			'name_size'     => get_post_meta( $collection_id, '_srm_collection_name_font_size', true ),
			'meta_just'     => get_post_meta( $collection_id, '_srm_collection_meta_justification', true ),
			'meta_font'     => get_post_meta( $collection_id, '_srm_collection_meta_font', true ),
			'meta_size'     => get_post_meta( $collection_id, '_srm_collection_meta_font_size', true ),
			'body_just'     => get_post_meta( $collection_id, '_srm_collection_body_justification', true ),
			'body_font'     => get_post_meta( $collection_id, '_srm_collection_body_font', true ),
			'body_size'     => get_post_meta( $collection_id, '_srm_collection_body_font_size', true ),
			'color'         => get_post_meta( $collection_id, '_srm_collection_color', true ),
			'avatar_pos'    => get_post_meta( $collection_id, '_srm_collection_avatar_position', true ),
			'avatar_size'   => get_post_meta( $collection_id, '_srm_collection_avatar_size', true ),
		);

	}

	/**
	 * Return the array of pages or posts where the collection shortcode is being used.
	 *
	 * @param $collection_id
	 *
	 * @return array|object|null
	 */
	public static function get_collection_usage( $collection_id ) {

		global $wpdb;
		$query = "SELECT ID, post_title, post_type FROM ".$wpdb->posts." WHERE post_content LIKE '%[starfish%collection=%{$collection_id}%' AND post_status = 'publish'";

		return $wpdb->get_results ($query);

	}

    /**
     * Get total collection count
     *
     * @return mixed
     */
    public static function srm_get_total_collections() {
        $args           = array(
            'post_type'      => array( 'collection' ),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        $starfish_query = new WP_Query( $args );

        return $starfish_query->post_count;
    }

    /**
     * Set all extra collections past plan limit to draft status
     *
     * @param integer $limit
     *
     * @return integer
     */
    public static function srm_force_extra_collections_to_draft( $limit )
    {
        global $wpdb;
        $count = self::srm_get_total_collections();
        if(empty($limit)) {
            $violations = 0; // Account for unlimited plans
        } else {
            $violations = $count - $limit;
        }

        if($violations > 0 ) {
            $args = array(
                'post_type'      => array( 'collection' ),
                'orderby'        => 'created',
                'order'          => 'DESC',
                'posts_per_page' => $violations,
            );
            $starfish_query = new WP_Query( $args );
            if ( $starfish_query->have_posts() ) {
                while ( $starfish_query->have_posts() ) {
                    $starfish_query->the_post();
                    if(get_post_status() !== 'draft') {
                        $srm_post_id = get_the_ID();
                        $updated = $wpdb->update($wpdb->posts, array('post_status' => 'draft'), array('ID' => $srm_post_id));
                        if( false === $updated ) {
                            SRM_LOGGING::addEntry(array(
                                "levl"    => SRM_LOGGING::SRM_ERROR,
                                "action"  => "SET_COLLECTIONS_TO_DRAFT",
                                "message" => $wpdb->last_error(),
                                "code"    => "SRM-C-CF2D-D"
                            ));
                        }
                    }
                }
            }
            wp_reset_postdata();
        }
        return $violations;
    }

}