<?php

use  Starfish\Cron as SRM_CRON ;
use  Starfish\Freemius as SRM_FREEMIUS ;
add_action( 'admin_bar_menu', 'srm_admin_bar', 500 );
function srm_admin_bar( WP_Admin_Bar $admin_bar )
{
    if ( !current_user_can( 'manage_options' ) ) {
        return;
    }
    // Starfish Parent
    $title = 'Starfish Reviews';
    $admin_bar->add_menu( array(
        'id'     => 'srm-admin-bar',
        'parent' => null,
        'group'  => null,
        'title'  => '<img class="srm-admin-bar-icon" src="' . SRM_PLUGIN_URL . '/assets/icon-128x128.png" style="width: 30px;"><span class="srm-admin-bar-next-scrape-alert" style="float: right; margin-left: 10px;">' . $title . '</span>',
        'href'   => admin_url( 'admin.php?page=starfish-settings' ),
        'meta'   => array(
        'title' => __( 'Starfish Settings' ),
    ),
    ) );
    // New Funnel
    $admin_bar->add_menu( array(
        'id'     => 'srm-admin-bar-new-funnel',
        'parent' => 'srm-admin-bar',
        'group'  => null,
        'title'  => 'New Funnel',
        'href'   => admin_url( 'post-new.php?post_type=funnel' ),
        'meta'   => array(
        'title' => __( 'Add a new Funnel', 'starfish' ),
    ),
    ) );
    // New Profile
    
    if ( SRM_FREEMIUS::starfish_fs()->can_use_premium_code() ) {
        $admin_bar->add_menu( array(
            'id'     => 'srm-admin-bar-new-profile',
            'parent' => 'srm-admin-bar',
            'group'  => null,
            'title'  => 'New Profile',
            'href'   => admin_url( 'admin.php?page=starfish-reviews-profiles' ),
            'meta'   => array(
            'title' => __( 'Add a new Profile', 'starfish' ),
        ),
        ) );
        // New Collection
        $admin_bar->add_menu( array(
            'id'     => 'srm-admin-bar-new-collection',
            'parent' => 'srm-admin-bar',
            'group'  => null,
            'title'  => 'New Collection',
            'href'   => admin_url( 'post-new.php?post_type=collection' ),
            'meta'   => array(
            'title' => __( 'Add a new Collection', 'starfish' ),
        ),
        ) );
    }

}
