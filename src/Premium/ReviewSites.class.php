<?php

namespace Starfish\Premium;

/**
 * Object builder for Review Sites supported by Review Shake
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    Release: 1.0.0
 * @link       https://api.supervisor.reviewshake.com
 * @see        UtilsReviews
 * @since      Release: 3.0.0
 */
class ReviewSites {

	public $name;
	public $url;
	public $logo;
	public $tier;

	function __construct( $name, $url, $logo, $tier ) {
		$this->name = $name;
		$this->url  = $url;
		$this->logo = $logo;
		$this->tier = $tier;
	}

}