<?php 

require_once('srm-header.php');
do_action('before-starfish-review-form');

$srm_funnel_id = get_the_ID();
echo do_shortcode('[starfish funnel="'.$srm_funnel_id.'"]');

do_action('after-starfish-review-form');

require_once('srm-footer.php');