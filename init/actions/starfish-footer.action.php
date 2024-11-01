<?php

use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Utils as SRM_UTILS;
use Starfish\Funnels as SRM_FUNNELS;

add_action( 'admin_footer', 'starfish_add_admin_footer_script' );
function starfish_add_admin_footer_script() {
	global $pagenow;
    global $post;
	$is_premium = SRM_FREEMIUS::starfish_fs()->can_use_premium_code();
	if ( $is_premium && (
	        SRM_UTILS::is_starfish_page($post, $pagenow, $_GET)
        )
	) {
		starfish_add_beacon_help_scout_button();
	}

}

function starfish_add_beacon_help_scout_button() {
	?>
	<script>
        !function (e, t, n) {
            function a() {
                var e = t.getElementsByTagName("script")[0], n = t.createElement("script");
                n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e)
            }

            if (e.Beacon = n = function (t, n, a) {
                e.Beacon.readyQueue.push({method: t, options: n, data: a})
            }, n.readyQueue = [], "complete" === t.readyState) return a();
            e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1)
        }(window, document, window.Beacon || function () {
        });
        window.Beacon('init', '392c4337-1f73-4e52-84e9-d226d7d3a21d')
    </script>
	<?php
	if ( SRM_FREEMIUS::starfish_fs()->can_use_premium_code() ) {

		$fs_user  = SRM_FREEMIUS::starfish_fs()->get_user();
		$fs_name  = $fs_user->get_name();
		$fs_email = $fs_user->email;
		$funnels  = SRM_FUNNELS::srm_get_funnels();
		$current_funnels = null;
        foreach($funnels as $funnel) {
            $current_funnels[] = 'funnel_' . $funnel->ID . ': "' . get_permalink($funnel->ID) . '",';
        }
		?>
		<script>
            Beacon("identify", {
                name: "<?php echo esc_attr( $fs_name ); ?>",
                email: "<?php echo esc_attr( $fs_email ); ?>",
	            signature: "<?php echo hash_hmac('sha256', $fs_email, 'H9EPg4pra/issFPvcYnBCvLjSFf4OBCA/heoWa0UTTg='); ?>",
                funnels: "<?php echo json_encode($current_funnels); ?>"
            });
            Beacon('prefill', {
                subject: 'Starfish Contact From Plugin Admin'
            })
		</script>

		<?php
	}
}
