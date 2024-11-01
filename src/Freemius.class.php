<?php

namespace Starfish;

use  Freemius_Exception ;
use  Freemius_Api_WordPress as FSAPI ;
use  Starfish\Logging as SRM_LOGGING ;
use function  fs_dynamic_init ;
/**
 * FREEMIUS
 *
 * Main class for managing Starfish Freemius instance
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1.0
 * @author     Matt Galloway <matt@starfish.reviews.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://freemius.com/help/documentation/wordpress-sdk/
 * @since      2.0
 */
class Freemius
{
    public const  FS__API_SCOPE = 'developer' ;
    public const  FS__API_DEV_ID = 12225 ;
    public const  FS__API_PUBLIC_KEY = 'pk_fc912d212f35944d0c6bde6b10379' ;
    public const  FS__API_SECRET_KEY = 'sk_2<.Tp@8_i{m7B9E;46};mBi3p~<Hh' ;
    public const  FS__API_PLUGIN_ID = 2029 ;
    public const  FS__FTR_FUNNELS_ID = 2342 ;
    public const  FS__FTR_PROFILES_ID = 9819 ;
    public const  FS__FTR_COLLECTIONS_ID = 9821 ;
    public const  FS__PLAN_AGENCY_ID = 14297 ;
    /**
     * Holds Freemius Object
     *
     * @var object
     */
    private static  $starfish_fs ;
    /**
     * Holds Freemius API Object
     *
     * @var object
     */
    private static  $starfish_fs_api ;
    /**
     * Holds Freemius Plan Feature Limits
     *
     * @var object
     */
    private static  $starfish_plan_limits ;
    /**
     * SRM_FREEMIUS constructor.
     */
    public function __construct()
    {
        // Init Freemius!
        $this->starfish_fs();
        // Init Freemius API
        $this->starfish_fsapi();
    }
    
    /**
     * @return Freemius_Api
     */
    public static function starfish_fsapi()
    {
        
        if ( empty(self::$starfish_fs_api) ) {
            require_once WP_FS__DIR_SDK . '/FreemiusWordPress.php';
            self::$starfish_fs_api = new FSAPI(
                self::FS__API_SCOPE,
                self::FS__API_DEV_ID,
                self::FS__API_PUBLIC_KEY,
                self::FS__API_SECRET_KEY
            );
        }
        
        return self::$starfish_fs_api;
    }
    
    /**
     * Freemius object and configuration
     *
     * @return Freemius
     * @throws Freemius_Exception|Freemius_Exception Freemius exception.
     */
    public static function starfish_fs()
    {
        
        if ( empty(self::$starfish_fs) ) {
            // Include Freemius SDK.
            require_once SRM_PLUGIN_PATH . 'vendor/freemius/wordpress-sdk/start.php';
            self::$starfish_fs = fs_dynamic_init( array(
                'id'               => '2029',
                'slug'             => 'starfish-reviews',
                'type'             => 'plugin',
                'public_key'       => 'pk_ada90fa5edfa2bbaa34f177f505b6',
                'is_premium'       => false,
                'is_premium_only'  => false,
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'trial'            => array(
                'days'               => 14,
                'is_require_payment' => true,
            ),
                'has_affiliation'  => 'selected',
                'menu'             => array(
                'slug'       => 'starfish-settings',
                'first-path' => 'admin.php?page=starfish-settings',
                'parent'     => array(
                'slug' => 'starfish-parent',
            ),
                'support'    => true,
                'pricing'    => true,
                'contact'    => true,
            ),
                'is_live'          => true,
            ) );
            self::$starfish_fs->set_basename( true, SRM_MAIN_FILE );
            self::$starfish_fs->add_filter( 'plugin_icon', array( __CLASS__, 'starfish_fs_custom_icon' ) );
            self::$starfish_fs->add_filter( 'license_key', array( __CLASS__, 'starfish_wc_license_key_filter' ) );
            self::$starfish_fs->add_filter( 'license_key_maxlength', array( __CLASS__, 'starfish_wc_license_key_maxlength_filter' ) );
            self::$starfish_fs->add_filter( 'hide_license_key', '__return_true' );
            self::$starfish_fs->add_filter( 'connect_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            self::$starfish_fs->add_filter( 'after_skip_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            self::$starfish_fs->add_filter( 'after_connect_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            self::$starfish_fs->add_filter( 'after_pending_connect_url', array( __CLASS__, 'starfish_fs_settings_url' ) );
            self::$starfish_fs->add_filter( 'upload_and_install_video_url', 'https://docs.starfish.reviews/article/4-how-to-install-starfish-reviews' );
            self::$starfish_fs->add_filter( 'hide_freemius_powered_by', '__return_true' );
            self::$starfish_fs->add_filter( 'hide_billing_and_payments_info', '__return_true' );
            self::$starfish_fs->add_filter( 'freemius_pricing_js_path', array( __CLASS__, 'starfish_custom_pricing_js_path' ) );
            self::$starfish_fs->add_action( 'after_premium_version_activation', array( __CLASS__, 'starfish_fs_version_activation' ) );
            self::$starfish_fs->add_action( 'after_free_version_reactivation', array( __CLASS__, 'starfish_fs_version_activation' ) );
            self::$starfish_fs->add_action( 'after_license_change', array( __CLASS__, 'starfish_fs_version_activation' ) );
            // Override internationalization values.
            self::$starfish_fs->override_i18n( array(
                'opt-in-connect' => __( "Yes - I'm in!", 'starfish' ),
                'skip'           => __( 'Not today', 'starfish' ),
                'support-forum'  => __( 'Forum', 'starfish' ),
            ) );
        }
        
        return self::$starfish_fs;
    }
    
    /**
     * Delete the plans limit transient when Freemius license activation occurs (or reactivation of Free Version).
     */
    public static function starfish_fs_version_activation()
    {
        delete_transient( SRM_TRAN_PLAN_LIMITS );
    }
    
    /**
     * Set the Pricing page to use the new React-JS version
     *
     * @param string $default_pricing_js_path The default path.
     * @return string The custom pricing path.
     */
    public static function starfish_custom_pricing_js_path( $default_pricing_js_path )
    {
        return SRM_PLUGIN_PATH . '/src/freemius-pricing/freemius-pricing.js';
    }
    
    /**
     * Get the current plan's limit.
     *
     * @return array
     * @throws Freemius_Exception Freemius Exception.
     */
    public static function srm_get_plan_limit()
    {
        $plan_limits = get_transient( SRM_TRAN_PLAN_LIMITS );
        $transient_timeout = self::srm_get_transient_timeout( SRM_TRAN_PLAN_LIMITS );
        
        if ( !self::starfish_fs()->can_use_premium_code() ) {
            $plan_id = '3025';
        } else {
            $plan_id = self::starfish_fs()->get_plan_id();
        }
        
        
        if ( empty($plan_limits) || empty($transient_timeout) || $plan_limits['plan_id'] != $plan_id ) {
            $response = self::starfish_fsapi()->Api( '/plugins/' . self::FS__API_PLUGIN_ID . '/features.json?plan_id=' . $plan_id );
            
            if ( !$response || !isset( $response->features ) ) {
                $srm_error_cya = '01';
                SRM_LOGGING::addEntry( array(
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'Freemius Plan Features Response Failed (plan: ' . $plan_id . ')',
                    'message' => wp_json_encode( $response ),
                    'code'    => 'SRM_C_FRPL_X_' . $srm_error_cya,
                ) );
                // At least return some small limits to keep basic functionality while the error is being resolved.
                $plan_limits = array(
                    'plan_id'     => $plan_id,
                    'funnels'     => 5,
                    'profiles'    => 1,
                    'collections' => 1,
                );
            } else {
                $features = $response->features;
                $srm_error_cya = '02';
                SRM_LOGGING::addEntry( [
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'Freemius Plan Features Response (plan: ' . $plan_id . ')',
                    'message' => wp_json_encode( $features ),
                    'code'    => 'SRM_C_FRPL_X_' . $srm_error_cya,
                ] );
                $ftr_funnels = $features[array_search( self::FS__FTR_FUNNELS_ID, array_column( $features, 'id' ), false )];
                $ftr_profiles = $features[array_search( self::FS__FTR_PROFILES_ID, array_column( $features, 'id' ), false )];
                $ftr_collections = $features[array_search( self::FS__FTR_COLLECTIONS_ID, array_column( $features, 'id' ), false )];
                $plan_limits = array(
                    'plan_id'     => $plan_id,
                    'funnels'     => ( strtolower( $ftr_funnels->value ) === 'unlimited' ? null : $ftr_funnels->value ),
                    'profiles'    => ( strtolower( $ftr_profiles->value ) === 'unlimited' ? null : $ftr_profiles->value ),
                    'collections' => ( strtolower( $ftr_collections->value ) === 'unlimited' ? null : $ftr_collections->value ),
                );
            }
            
            set_transient( SRM_TRAN_PLAN_LIMITS, $plan_limits, 86400 );
        } else {
            $srm_error_cya = '03';
            SRM_LOGGING::addEntry( [
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Freemius Plan Feature Limits (plan: ' . $plan_id . ') - Next Validation in ' . $transient_timeout . ' seconds',
                'message' => print_r( $plan_limits, true ),
                'code'    => 'SRM_C_FRPL_X_' . $srm_error_cya,
            ] );
        }
        
        return $plan_limits;
    }
    
    /**
     * Set Starfish icon on activation opt-in screen
     *
     * @return mixed
     */
    public static function starfish_fs_custom_icon()
    {
        return SRM_PLUGIN_PATH . '/assets/icon.svg';
    }
    
    /**
     * Get the plugin settings URL for use in freemius redirects
     *
     * @return mixed
     */
    public static function starfish_fs_settings_url()
    {
        return admin_url( 'admin.php?page=starfish-settings' );
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
            $styles['premium_only_badge'] = '<a href="' . SRM_PLANS_URL . '" title="Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            $styles['container_class'] = 'premium-only-field';
            $styles['premium_only_link'] = '<br/><small>' . SRM_GO_PREMIUM_LINK . '</small>';
        }
        
        return $styles;
    }
    
    /**
     * @param $transient
     * @return int|null
     */
    public static function srm_get_transient_timeout( $transient )
    {
        global  $wpdb ;
        $transient_timeout = $wpdb->get_col( "\n\t      SELECT option_value\n\t      FROM {$wpdb->options}\n\t      WHERE option_name\n\t      LIKE '%_transient_timeout_{$transient}%'\n\t    " );
        
        if ( $transient_timeout ) {
            return $transient_timeout[0] - time();
        } else {
            return null;
        }
    
    }

}
if ( !class_exists( 'Freemius', true ) ) {
    return new Freemius();
}