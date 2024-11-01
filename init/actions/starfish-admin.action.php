<?php

add_action( 'admin_menu', 'settings_submenu' );
if ( SRM_FREEMIUS::starfish_fs()->can_use_premium_code() && function_exists( 'srm_plugin_admin_reports_menu__premium_only' ) ) {
    add_action( 'admin_menu', 'srm_plugin_admin_reports_menu__premium_only', 10 );
}
/**
* Add Settings Sub-menu page
*/
function settings_submenu()
{
    add_submenu_page(
        'edit.php?post_type=starfish_feedback',
        //Parent Slug
        esc_html__( 'Starfish Settings', 'starfish' ),
        // Page Title
        esc_html__( 'Settings', 'starfish' ),
        // Menu Title
        'administrator',
        // Capability required
        'starfish-settings',
        // Menu Slug
        'srm_admin_settings_page'
    );
}

/**
* Add Settings Page (callback)
*/
function srm_admin_settings_page()
{
    ?>
   <div class="wrap">
       <h1><?php 
    _e( 'Starfish Settings', 'starfish' );
    ?></h1>
       <?php 
    settings_errors();
    ?> 
       <div id="description"><?php 
    esc_html_e( 'Different options required for the basic operation of Starfish Reviews', 'starfish' );
    ?></div>
       <?php 
    $active_tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'general_options' );
    ?>
       <h2 class="nav-tab-wrapper">
           <a href="edit.php?post_type=starfish_feedback&page=starfish-settings&tab=general_options"
              class="nav-tab <?php 
    echo  ( $active_tab == 'general_options' ? 'nav-tab-active' : '' ) ;
    ?>"><?php 
    esc_html_e( 'General', 'starfish' );
    ?></a>
           <a href="edit.php?post_type=starfish_feedback&page=starfish-settings&tab=email_options"
              class="nav-tab <?php 
    echo  ( $active_tab == 'email_options' ? 'nav-tab-active' : '' ) ;
    ?>"><?php 
    esc_html_e( 'Email', 'starfish' );
    ?></a>
           <a href="edit.php?post_type=starfish_feedback&page=starfish-settings&tab=defaults_options"
              class="nav-tab <?php 
    echo  ( $active_tab == 'defaults_options' ? 'nav-tab-active' : '' ) ;
    ?>"><?php 
    esc_html_e( 'Defaults', 'starfish' );
    ?></a>
       </h2>
       <form method="post" action="options.php">
       <?php 
    // This prints out all hidden setting fields
    
    if ( $active_tab == 'general_options' ) {
        settings_fields( SRM_OPTION_GROUP_GENERAL );
        do_settings_sections( SRM_OPTION_GROUP_GENERAL );
    } elseif ( $active_tab == 'email_options' ) {
        settings_fields( SRM_OPTION_GROUP_EMAIL );
        do_settings_sections( SRM_OPTION_GROUP_EMAIL );
    } else {
        settings_fields( SRM_OPTION_GROUP_DEFAULTS );
        do_settings_sections( SRM_OPTION_GROUP_DEFAULTS );
    }
    
    // end if/else
    submit_button();
    ?>
       </form>
   </div>
   <?php 
}
