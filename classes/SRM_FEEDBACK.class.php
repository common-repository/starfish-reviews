<?php
/**
 * REVIEWS
 *
 * Main class for managing Starfish Feedback instance
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0
 * @author  silvercolt45 <webmaster@silvercolt.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since  2.0.0
 */
class SRM_FEEDBACK {

	public static function srm_restrict_control( $plan ) {

        $restricted = true;
		if ( strtolower( $plan ) != 'free' || SRM_FREEMIUS::starfish_fs()->can_use_premium_code() ) {
            $restricted = false;
		}
		return $restricted;

	}

	public static function srm_get_total_feedback() {

		global $wpdb;
		$args           = array(
			'post_type'      => array( 'starfish_feedback' ),
			'status'         => 'published',
			'posts_per_page' => -1,
		);
		$starfish_query = new WP_Query( $args );
		return $starfish_query->post_count;

	}

    public static function srm_get_feedback( $funnel_id, $month ) {

        global $wpdb;
        $args = array(
            'post_type'      => array( 'starfish_feedback' ),
            'status'         => 'published',
            'posts_per_page' => -1,
        );
        if( 'all' !== $funnel_id && !empty($funnel_id) ) {
            $args['meta_query'] = array(
                    array(
                        'key'   => SRM_META_FUNNEL_ID,
                        'value' => $funnel_id,
                    )
            );
        }
        if( '0' !== $month && !empty($month) ) {
            $args['m'] = intval( $month );
        }

        $starfish_query = new WP_Query( $args );
        return $starfish_query;

    }

    public static function srm_get_recent_feedback_count( $days_ago ) {

        global $wpdb;
        $args           = array(
            'post_type'      => array( 'starfish_feedback' ),
            'status'         => 'published',
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'after'     => strtotime("-$days_ago day"),
                    'before'    => strtotime("now"),
                    'inclusive' => true,
                ),
            ),
        );
        $starfish_query = new WP_Query( $args );
        return $starfish_query->post_count;

    }

	public static function srm_admin_plan_restriction_notice( $plan ) {

		( empty( $plan ) || 'PLAN_TITLE' ) ? $plan = 'FREE' : $plan;
		ob_start(); ?>
		<div class="notice notice-warning">
		  <p>
			  <?php
				echo sprintf(
					__( 'Your current plan (%1$s) supports Ready-Only view of the captured Feeedback. %2$s', 'starfish' ),
					$plan,
					'<a href="' . SRM_PLANS_URL . '" title="' . __( 'Upgrade Starfish Plan', 'starfish' ) . '">' . __( 'Upgrade your plan!', 'starfish' ) . '</a>"'
				);
				?>
		  </p>
		</div>
		<?php
		return ob_get_clean();

	}

    /**
     * Get list of any exported feedback files.
     *
     * @return array|false
     */
	public static function srm_admin_get_feedback_exports() {

	    $files   = array_diff(scandir( SRM_PLUGIN_PATH . '/exports' ), array('.', '..','.gitignore'));
	    $exports = false;
	    foreach($files as $file) {
	        $exports[] = array(
	            'file' => $file,
                'name' => basename(SRM_PLUGIN_PATH . '/exports/' . $file, ".csv"),
                'url'  => SRM_PLUGIN_URL . '/exports/' . $file,
            );
        }
	    return $exports;

    }

}

if ( class_exists( 'SRM_FEEDBACK', true ) ) {
	return new SRM_FEEDBACK();
}
