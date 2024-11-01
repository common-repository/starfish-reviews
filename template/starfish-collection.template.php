<?php

use Starfish\Twig as SRM_TWIG;
use Starfish\Premium\Collections as SRM_COLLECTIONS;

require_once 'starfish-header.template.php';

// Do some cool stuff before the funnel is loaded.
do_action('starfish_before_collection');

// Get the current post's ID and template variables.
$collection_id      = get_the_ID();
$template_variables = SRM_COLLECTIONS::srm_get_collection_variables($collection_id);

// Build Template.
$twig = SRM_TWIG::get_twig();
echo $twig->render('starfish-collection.html.twig', $template_variables);

// Do some cool stuff after the funnel is loaded.
do_action('starfish_after_collection');

require_once 'starfish-footer.template.php';
