<?php

use Starfish\Premium\Collections as SRM_COLLECTIONS;

/**
 * Callbacks for Collections Ajax requests
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0.0
 * @link       https://api.supervisor.reviewshake.com
 * @see        Collections
 * @since      Release: 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function srm_collection_hide() {
    $is_hidden  = rest_sanitize_boolean( $_POST['is_hidden'] );
    $collection_id = sanitize_text_field( $_POST['collection_id'] );
    if ( ! check_admin_referer(
        'srm_collection',
        'security'
    )
    ) {
        echo wp_json_encode(
            array(
                'success' => false,
                'message' => 'You do not have rights to perform this action',
            )
        );
        wp_die();
    } else {
        if ( SRM_COLLECTIONS::update_collection_hide( $collection_id, $is_hidden ) !== 0 ) {
            echo wp_json_encode(
                array(
                    'success' => true,
                    'message' => 'Successfully Updated Collection Hidden flag',
                )
            );
            wp_die();
        } else {
            echo wp_json_encode(
                array(
                    'success' => false,
                    'message' => 'Failed to update Collection Hidden flag',
                )
            );
            wp_die();
        }
    }
}

add_action( 'wp_ajax_srm-collection-hide', 'srm_collection_hide' );