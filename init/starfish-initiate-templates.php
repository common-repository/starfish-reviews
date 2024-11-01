<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

global $SRM_TWIG;
global $SRM_COMPOSER_LOADER;

$options             = array(
    'debug'             => true,
    'auto_reload '      => true,
    'strict_variables ' => false
);
$SRM_COMPOSER_LOADER = new FilesystemLoader(SRM_PLUGIN_PATH . '/templates/twig');
$SRM_TWIG            = new Environment($SRM_COMPOSER_LOADER, $options);
