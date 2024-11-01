<?php
/**
 * Plugin Name: Starfish Reviews
 * Plugin URI:  https://starfish.reviews
 * Description: The #1 review generation and review marketing plugin for WordPress! Encourage your tribe to provide 5-star reviews on Google, Facebook, and many more.
 * Author: Starfish Reviews
 * Version: 3.1.12
 * Author URI: https://starfish.reviews
 * Copyright: 2022 Starfish Reviews, LLC
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: starfish
 *
 * @package         WordPress
 * @subpackage      starfish
   */
if (!defined('ABSPATH')) {
	exit;
}
if (defined('SRM_PLAN_TITLE') && (SRM_PLAN_TITLE == 'FREE' || SRM_PLAN_TITLE == 'PLAN_TITLE')) {
	$message = __('Please <strong>deactivate</strong> and uninstall the <strong>Free</strong> version of <strong>Starfish Reviews</strong> before trying to activate the Premium version (' . SRM_PLUGIN_BASENAME . ')', 'starfish');
	echo '<div class="notice notice-error srm-notice is-dismissible">
        <div class="srm-notice-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="srm-notice-content">' . $message . '</div>
    </div>';
} else {
	// Core Constants.
	define('SRM_VERSION', '3.1.12');
	define('SRM_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
	define('SRM_MAIN_FILE', __FILE__);
	define('SRM_PLUGIN_PATH', plugin_dir_path(__FILE__));
	define('SRM_PLUGIN_BASENAME', plugin_basename(__FILE__));
	// Initiate Plugin.
	include_once SRM_PLUGIN_PATH . '/vendor/autoload.php';
	require_once SRM_PLUGIN_PATH . '/src/StarfishReviews.class.php';
	Starfish\StarfishReviews::init();
}