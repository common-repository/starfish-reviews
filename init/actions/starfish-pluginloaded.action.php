<?php

use  Starfish\Activation as SRM_ACTIVATION ;
use  Starfish\Cron as SRM_CRON ;
use  Starfish\Freemius as SRM_FREEMIUS ;
use  Starfish\Premium\ReviewsPage as SRM_REVIEWS_PAGE ;
use  Starfish\Premium\ReviewsProfilesPage as SRM_REVIEWS_PROFILES_PAGE ;
// Auto activate plugin using wp-config.php definition
// Requires the following line in wp-config.php: `define( 'WP__STARFISH_FS__LICENSE_KEY', '<YOUR_LICENSE_KEY>' );`

if ( defined( 'WP__STARFISH_FS__LICENSE_KEY' ) ) {
    $fs_license_activator = new SRM_ACTIVATION();
    add_action( 'admin_init', array( $fs_license_activator, 'license_key_auto_activation' ) );
}

/**
 * Initialize Plugin
 *
 * @throws Freemius_Exception
 */
function srm_plugins_loaded()
{
    // Check if uploads directory exists, if not create it.
    if ( !is_dir( SRM_UPLOADS_DIR ) ) {
        mkdir( SRM_UPLOADS_DIR );
    }
}

add_action( 'plugins_loaded', 'srm_plugins_loaded', 0 );
/**
 * @return void
 */
function srm_register_all_scripts()
{
    // Styles.
    wp_register_style(
        'jquery-ui',
        'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css',
        false,
        '1.12.1',
        'all'
    );
    wp_register_style(
        'srm-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        false,
        '6.0.0',
        'all'
    );
    //	wp_register_style( 'srm-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css', false, '5.2.3', 'all' );
    wp_register_style(
        'srm-bootstrap',
        SRM_PLUGIN_URL . '/css/bootstrap-srm.min.css',
        false,
        '5.2.3',
        'all'
    );
    // Scripts.
    wp_register_script(
        'jquery-ui',
        'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
        null,
        '1.12.1',
        false
    );
    wp_register_script(
        'srm-bootstrap',
        'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.bundle.min.js',
        null,
        '5.2.3',
        false
    );
}

add_action( 'wp_loaded', 'srm_register_all_scripts' );