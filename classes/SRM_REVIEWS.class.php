<?php
/**
 * REVIEWS
 *
 * Main class for managing Starfish Reviews instance
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0.0
 * @author  silvercolt45 <webmaster@silvercolt.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since  2.0.0
 */
class SRM_REVIEWS {
    
    function __construct() { }
    
    static public function srm_restrict_control( $plan ) {

        if ( strtolower( $plan ) == "free" || !SRM_FREEMIUS::starfish_fs()->can_use_premium_code() ) {
            return true;
        }
        else {
            return false;
        }

    }
    
    static public function srm_get_total_reviews() {
        
        global $wpdb;
	$args = array(
		'post_type'  => array('starfish_review'),
		'status' => 'published',
		'posts_per_page' => -1,
	);
	$starfish_query = new WP_Query( $args );
	return $starfish_query->post_count;
        
    }
    
    static public function srm_admin_plan_restriction_notice( $plan ) {

        (empty($plan) || 'PLAN_TITLE') ? $plan = "FREE" : $plan;
        ob_start(); ?>
        <div class="notice notice-warning">
          <p>
              <?php _e( "Your current plan ($plan) only supports Ready-Only view of the captured Reviews.  <a href='" . SRM_PLANS_URL . "' title='Upgrade Starfish Plan'>Upgrade your plan!</a>", "starfish" ); ?>
          </p>
        </div>
        <?php $content = ob_get_clean();
        return $content; 
        
    }
        
}

if (class_exists('SRM_REVIEWS', true)) {
    return new SRM_REVIEWS;
}