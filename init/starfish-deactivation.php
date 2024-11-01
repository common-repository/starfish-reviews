<?php

use  Starfish\Freemius as SRM_FREEMIUS ;
use  Starfish\Cron as SRM_CRON ;
/**
 * Uninstall default data
 */
function srm_plugin_uninstall()
{
    require_once SRM_PLUGIN_PATH . 'inc/uninstall-plugin-data.php';
}
