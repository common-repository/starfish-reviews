<?php

/**
 * Setting the template for a single Funnel Custom Post Type
 *
 * @version 1
 */

use Starfish\Twig as SRM_TWIG;
use Starfish\Funnels as SRM_FUNNELS;
use Twig\TwigFilter;

require_once 'starfish-header.template.php';

// Do some cool stuff before the funnel is loaded.
do_action( 'starfish_before_funnel_form' );

// Get the current post's ID and template variables.
$funnel_id          = get_the_ID();
$template_variables = SRM_FUNNELS::srm_localize_funnel_scripts( $funnel_id );

// Build Template.
$filter_get_icon_url = new TwigFilter( 'attachmentUrl', 'wp_get_attachment_url' );
$twig                = SRM_TWIG::get_twig();
$twig->addFilter( $filter_get_icon_url );
echo $twig->render( 'starfish-funnel-stepper.html.twig', $template_variables );

// Do some cool stuff after the funnel is loaded.
do_action( 'starfish_after_funnel_form' );

require_once 'starfish-footer.template.php';
