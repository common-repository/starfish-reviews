<?php

namespace Starfish;

use Starfish\Logging as SRM_LOGGING;
use JoliCode\Slack\ClientFactory as SLACK;
use JoliCode\Slack\Exception\SlackErrorResponse;

/**
 * SUPPORT
 *
 * Support functions used by Starfish (e.g., sending notifications of specific events to Starfish Reviews Support channels)
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1.0
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since      3.0
 */
class Support
{
    private const SLACK_TOKEN = 'xapp-1-A02QXP1SNJU-3695026876898-56796b2923f9d796be738bc1cfcaf16684ab7ad7804643998a32833e3c16b50e';

    public static function srm_slack()
    {
        global $starfish_slack;
        if (empty($starfish_slack)) {
            $starfish_slack = SLACK::create(self::SLACK_TOKEN);
        }
        return $starfish_slack;
    }

    public static function srm_post_message(array $blocks, string $title = 'Production Error Alert')
    {
        $srm_error_cya            = '01';
        $is_notifications_enabled = get_option(SRM_OPTION_SUPPORT_ALERTS, false);
        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'Support Notification Option',
                'message' => 'value=' . $is_notifications_enabled,
                'code'    => 'SRM_C_PCB_X_' . $srm_error_cya,
            )
        );
        if ($is_notifications_enabled || $is_notifications_enabled == 'yes') {
            $srm_error_cya = '02';
            $args          = array(
                'username' => 'starfish',
                'channel'  => 'support',
                'text'     => $title,
                'blocks'   => json_encode($blocks),
            );
            try {
                self::srm_slack()->chatPostMessage($args);
                SRM_LOGGING::addEntry(
                    array(
                        'level'   => SRM_LOGGING::SRM_INFO,
                        'action'  => 'Support Notification Sent',
                        'message' => 'title=' . $title,
                        'code'    => 'SRM_C_PCB_X_' . $srm_error_cya,
                    )
                );
            } catch (SlackErrorResponse $e) {
                SRM_LOGGING::addEntry(
                    array(
                        'level'   => SRM_LOGGING::SRM_ERROR,
                        'action'  => $title,
                        'message' => $e->getMessage(),
                        'code'    => 'SRM_C_SPM_X_' . $srm_error_cya,
                    )
                );
            }
        }
    }
}
