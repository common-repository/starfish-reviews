<?php

namespace Starfish;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * TWIG
 *
 * Main class for managing TWIG instance
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://twig.symfony.com/
 * @since      2.1
 */
class Twig
{
    /**
     * Building and returning the TWIG object
     *
     * @param null $template_path The twig template.
     * @param null $options       The twig options.
     *
     * @return Environment The new Twig object.
     */
    public static function get_twig($template_path = null, $options = null)
    {
        if (empty($template_path)) {
            $template_path = SRM_PLUGIN_PATH . '/template/twig';
        }
        if (empty($options)) {
            $options = array(
                'debug'             => false,
                'auto_reload'       => true,
                'strict_variables ' => false,
            );
        }
        $loader = new FilesystemLoader($template_path);

        return new Environment($loader, $options);
    }

}
