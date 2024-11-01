<?php

use  Starfish\Freemius as SRM_FREEMIUS ;
if ( SRM_FREEMIUS::starfish_fs()->can_use_premium_code__premium_only() && function_exists( 'srm_plugin_admin_reports_menu__premium_only' ) ) {
    add_action( 'admin_menu', 'srm_plugin_admin_reports_menu__premium_only', 10 );
}