<?php

namespace Starfish;

/**
 * Object builder for Funnel Icons
 *
 * @category   Funnels
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0
 * @link       https://api.supervisor.reviewshake.com
 * @see        UtilsReviews
 * @since      Release: 2.4.0
 */
class FunnelIcons
{
    public $name;
    public $class;

    public function __construct($name, $class)
    {
        $this->name = $name;
        $this->class = $class;
    }
}
