<?php

namespace Starfish;

use function esc_html;
use function file_exists;
use function file_get_contents;
use function file_put_contents;

use const FILE_APPEND;
use const SRM_LOG_FILE;

/**
 * FUNNELS
 *
 * Main class for managing Starfish Logging
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1.0
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @since      3.0
 */
class Logging
{
    public const SRM_ERROR = 'ERROR';
    public const SRM_WARN  = 'WARN';
    public const SRM_INFO  = 'INFO';
    public const SRM_DEBUG = 'DEBUG';

    public function __construct()
    {

        if (!file_exists(SRM_LOG_FILE)) {
            file_put_contents(SRM_LOG_FILE, null);
        }
    }

    /**
     * Add new logging entry to the master log file
     *
     * WARNING: Be sure and use print_r($value, true) for any variables being added to the entry elements
     *
     * <pre>
     * array (
     *      "type"      => ERROR|INFO|WARN|DEBUG
     *      "action"    => "String",
     *      "message"   => "String",
     *      "code"      => "String"
     * )
     * </pre>
     *
     * @param array $content
     */
    public static function addEntry(array $content)
    {

        // Check if logging is enabled
        $datetime = new \DateTime();
        $now 	  = $datetime->createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        if (get_option(SRM_OPTION_LOGGING)) {
            $level     = $content['level'];
            $timestamp = $now->format('d-m-Y G:i:s.u e');
            $action    = $content['action'];
            $message   = $content['message'];
            $code      = $content['code'];
            $entry     = $level . ' ' . $timestamp . ' action="' . $action . '" message="' . $message . '" code="' . $code . '"';
            file_put_contents(SRM_LOG_FILE, $entry . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Purge all log entries
     *
     * @returns boolean
     */
    public static function purgeLogs()
    {
        if (file_exists(SRM_LOG_FILE)) {
            file_put_contents(SRM_LOG_FILE, '');
            if (empty(file_get_contents(SRM_LOG_FILE))) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public static function human_filesize($bytes, $dec = 2): string
    {

        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0) {
            $dec = 0;
        }

        return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);

    }

}
