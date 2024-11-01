<?php

/**
 * FREEMIUS
 *
 * Main class for managing Starfish Freemius instance
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0.0
 * @author  silvercolt45 <webmaster@silvercolt.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link https://freemius.com/help/documentation/wordpress-sdk/
 * @since  2.0.0
 */
class SRM_FREEMIUS
{
    const  PLANS_URL = SRM_PLANS_URL ;
    /**
     * SRM_FREEMIUS constructor.
     */
    public function __construct()
    {
        // Init Freemius!
        $this->starfish_fs();
        // Update Plans URL if Active License
        if ( $this->starfish_fs()->has_any_active_valid_license() ) {
            $this->PLANS_URL .= '?has_license=true';
        }
        // Signal that SDK was initiated!
        do_action( 'starfish_fs_loaded' );
    }
    
    /**
     * Freemius object and configuration
     *
     * @return Freemius
     * @throws Freemius_Exception Freemius exception.
     */
    public static function starfish_fs()
    {
        global  $starfish_fs ;
        
        if ( !isset( $starfish_fs ) ) {
            // Include Freemius SDK.
            require_once SRM_PLUGIN_PATH . 'composer/vendor/freemius/wordpress-sdk/start.php';
            $starfish_fs = fs_dynamic_init( array(
                'id'              => '2029',
                'slug'            => 'starfish-reviews',
                'type'            => 'plugin',
                'public_key'      => 'pk_ada90fa5edfa2bbaa34f177f505b6',
                'is_premium'      => false,
                'is_premium_only' => false,
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'navigation'      => 'tabs',
                'menu'            => array(
                'slug'       => 'starfish-settings',
                'first-path' => 'plugins.php',
                'parent'     => array(
                'slug' => 'edit.php?post_type=starfish_feedback',
            ),
                'support'    => false,
                'pricing'    => false,
                'contact'    => false,
            ),
                'is_live'         => true,
            ) );
            $starfish_fs->add_filter( 'license_key', array( __CLASS__, 'starfish_wc_license_key_filter' ) );
            $starfish_fs->add_filter( 'license_key_maxlength', array( __CLASS__, 'starfish_wc_license_key_maxlength_filter' ) );
            $starfish_fs->add_filter( 'hide_license_key', '__return_true' );
            $starfish_fs->add_filter( 'connect_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            $starfish_fs->add_filter( 'after_skip_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            $starfish_fs->add_filter( 'after_connect_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            $starfish_fs->add_filter( 'after_pending_connect_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            $starfish_fs->add_filter( 'upload_and_install_video_url', 'https://docs.starfish.reviews/article/4-how-to-install-starfish-reviews' );
        }
        
        return $starfish_fs;
    }
    
    /**
     * Get the plugin settings URL for use in freemius redirects
     *
     * @return mixed
     */
    public static function starfish_fs_settings_url()
    {
        return admin_url( 'edit.php?post_type=starfish_feedback&page=starfish-settings&tab=general_options' );
    }
    
    /**
     * WC License Filter
     *
     * @param String $license_key The license key.
     *
     * @return bool|string
     */
    public static function starfish_wc_license_key_filter( $license_key )
    {
        if ( 0 === strpos( $license_key, 'wc_order_' ) ) {
            return substr( $license_key, -32 );
        }
        return $license_key;
    }
    
    /**
     * The WC max length of the key filter
     *
     * @param Integer $maxlength The max length of the key.
     *
     * @return int
     */
    public static function starfish_wc_license_key_maxlength_filter( $maxlength )
    {
        if ( $maxlength != 38 ) {
            $maxlength = 38;
        }
        return $maxlength;
    }
    
    /**
     * Return premium only styles for building premium only messages
     *
     * @return array
     * @throws Freemius_Exception Freemius exception.
     */
    public static function starfish_premium_only_styles()
    {
        $is_premium = self::starfish_fs()->can_use_premium_code();
        $styles = array();
        
        if ( $is_premium ) {
            $styles['disabled'] = null;
            $styles['readonly'] = null;
            $styles['premium_only_badge'] = null;
            $styles['container_class'] = null;
            $styles['premium_only_link'] = null;
        } else {
            $styles['disabled'] = 'disabled';
            $styles['readonly'] = 'readonly';
            $styles['premium_only_badge'] = '<a href="' . self::PLANS_URL . '" title="Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            $styles['container_class'] = 'premium-only-field';
            $styles['premium_only_link'] = '<br/><small><a class="premium-only-link" href="' . self::PLANS_URL . '" title="Upgrade Now!">Premium Feature</a></small>';
        }
        
        return $styles;
    }
    
    /**
     * Premium Notice
     *
     * @return string
     */
    public static function starfish_premium_notice()
    {
        $plans_url = self::PLANS_URL;
        return <<<EOD
<div class="srm-notice-message">
<i class="fas fa-exclamation-circle fa-2x"> Starfish Notice</i><br/>
Looks like you're trying to use premium Starfish features without a paid license. The free version only includes one single destination funnel, and does not include shortcode functionality. Please
 check those settings or <a href="{$plans_url}" title="Upgrade to Starfish Premium" target="_blank">upgrade to a premium plan</a>.
</div>
EOD;
    }

}
if ( !class_exists( 'SRM_FREEMIUS', true ) ) {
    return new SRM_FREEMIUS();
}