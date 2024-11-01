<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * TWIG
 *
 * Main class for managing TWIG instance
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0
 * @author  silvercolt45 <webmaster@silvercolt.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link https://twig.symfony.com/
 * @since  2.0.1.0
 */

class SRM_TWIG {

	public static function get_twig( $template_path = null, $options = null )  {
		if(empty($template_path)) {
			$template_path = SRM_PLUGIN_PATH . '/template/twig';
		}
		if(empty($options)) {
			$options = [
				'debug'             => true,
				'auto_reload'       => true,
				'strict_variables ' => false
			];
		}
		$loader = new FilesystemLoader( $template_path );
        return new Environment( $loader, $options );
	}

}

if (!class_exists('SRM_TWIG', true)) {
	return new SRM_TWIG;
}


