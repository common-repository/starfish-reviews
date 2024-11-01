<?php

namespace Starfish;

use Exception;
use Starfish\Freemius as SRM_FREEMIUS;
use UnexpectedValueException;

use function in_array;

/**
 * Class SRM_MIGRATIONS
 *
 * @category   Utility
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author     silvercolt45 <webmaster@silvercolt.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version    1.1
 * @link       none
 * @since      Release: 2.3
 */
class Migrations
{
    /**
     * SRM_MIGRATIONS constructor.
     */
    public function __construct()
    {
        // Check for new migration option.
        if (empty(self::get_schema())) {
            self::update_schema(
                array(
                    'version'    => '0',
                    'migrations' => array(),
                )
            );
        }
    }

    /**
     * Get current schema option
     *
     * @return object|boolean SRM_MIGRATIONS option
     */
    public static function get_schema()
    {
        return get_option(SRM_MIGRATIONS, false);
    }

    /**
     * Update the SRM_MIGRATION option (schema)
     *
     * @param array $schema New schema object.
     *
     * @return boolean Success of update.
     */
    public static function update_schema($schema)
    {
        return update_option(SRM_MIGRATIONS, $schema);
    }

    /**
     * Perform any pending migrations,
     * set transient array of all successful migrations for use in Admin Notices
     *
     * @return array $successful_migrations List of successful migrations
     * @throws Exception Update schema exception.
     */
    public static function execute_pending()
    {
        global $wpdb;
        $successful_migrations = array();
        $failed_migrations     = array();
        $schema                = self::get_schema();
        foreach (self::get_pending() as $migration) {
            $migration->executed_on = date('Y-m-d H:i:s');
            $version_key            = array_search($migration->version, array_column($schema['migrations'], 'version'), true);
            $is_authorized          = false;
            if ($migration->is_premium && SRM_FREEMIUS::starfish_fs()->is_premium()) {
                $is_authorized = true;
            }
            try {
                // Execute SQL scripts if applicable
                //TODO: need to break this up into two different migration paths/logic between Free and Premium scripts
                // so they have their own version history in case migrations skip over each other and end up never
                // getting executed because of the version number
                if ($migration->data && $is_authorized) {
                    $sql = SRM_PLUGIN_PATH . 'migrations/' . $migration->version . '/db.sql';
                    $query = file_get_contents($sql);
                    if (file_exists($sql) && !empty($query)) {
                        $parts = explode('#new_query', $query);
                        foreach ($parts as $query_index => $part) {
                            $response = $wpdb->query($wpdb->prepare($part, $wpdb->prefix, $wpdb->dbname)); // no cache ok. db call ok.
                            if ($response === false) {
                                throw new Exception(
                                    esc_html__(
                                        'Failed to migration Database Schema: Partial Execution Failure (part ' . ($query_index + 1) . ' of '
                                            . sizeof($parts) . ')  ERROR: ' . $wpdb->last_error,
                                        'starfish'
                                    )
                                );
                            }
                        }
                    } else {
                        throw new UnexpectedValueException(esc_html__('Failed to migration Database Schema: SQL File Invalid', 'starfish'));
                    }
                }
                include_once SRM_PLUGIN_PATH . 'migrations/' . $migration->version . '/migrate.php';
                $migration->status  = 'COMPLETE';
                $migration->comment = esc_html__('Successfully completed migration! ' . $success_message, 'starfish');
                if (false !== $version_key) {
                    array_splice($schema['migrations'], $version_key, 1);
                    array_push($schema['migrations'], $migration);
                    $schema['version'] = $migration->version;
                }
                if (!self::update_schema($schema)) {
                    $failed_migrations[] = $migration;
                    set_transient('srm_failed_migrations', $failed_migrations);
                    throw new UnexpectedValueException(esc_html__('Failed to update Starfish Migration schema', 'starfish'));
                } else {
                    $successful_migrations[] = $migration;
                }
            } catch (Exception $e) {
                $migration->status  = 'FAILED';
                $migration->comment = 'ERROR: ' . $e->getMessage();
                if (false !== $version_key) {
                    array_splice($schema['migrations'], $version_key, 1);
                    array_push($schema['migrations'], $migration);
                }
                if (!self::update_schema($schema)) {
                    $failed_migrations[] = $migration;
                    set_transient('srm_failed_migrations', $failed_migrations);
                    throw new UnexpectedValueException(esc_html__('Failed to update Starfish Migration schema', 'starfish'));
                }
            }
        }

        if (!empty($successful_migrations)) {
            set_transient('srm_successful_migrations', $successful_migrations, 5);
        }

        if (empty(self::get_pending())) {
            delete_transient('srm_pending_migrations');
        }

        if (empty(self::get_failures())) {
            delete_transient('srm_failed_migrations');
        }

        return $successful_migrations;
    }

    /**
     * Ingest any new migration configurations
     */
    public static function ingest()
    {
        $schema          = self::get_schema();
        $current_version = $schema['version'];
        /* Get list of migration config files (JSON) newer than current version. */
        $migration_dirs = glob(SRM_PLUGIN_PATH . 'migrations/*', GLOB_ONLYDIR);
        $new_versions   = self::get_new_versions($migration_dirs, $current_version, $schema);

        /* Aggregate new migrations */
        if (!empty($new_versions)) {
            foreach ($new_versions as $version) {
                $file = SRM_PLUGIN_PATH . 'migrations/' . $version . '/config.json';
                if (file_exists($file)) {
                    $config = json_decode(file_get_contents($file));
                    if (!in_array($config, $schema['migrations'], true)) {
                        $schema[ 'migrations' ][] = $config;
                    }
                }
            }
            /* Update migration option with new configs */
            self::update_schema($schema);
        }

        /* Get any failed or pending migrations */
        self::get_pending();
        self::get_failures();
    }

    /**
     * Get the new versions that have not yet been migrated.
     *
     * @param array  $migration_dirs
     * @param string $current_version
     * @param array  $schema
     *
     * @return array $new_versions
     */
    public static function get_new_versions(array $migration_dirs, string $current_version, array $schema)
    {
        $new_versions = array();
        foreach ($migration_dirs as $migration_dir) {
            $new_version = basename($migration_dir);
            if (floatval($new_version) > floatval($current_version)) {
                if (!array_search($new_version, $schema['migrations'])) {
                    $new_versions[] = $new_version;
                }
            }
        }

        return $new_versions;
    }

    /**
     * Get any pending migrations not yet completed
     *
     * @return Array $pending_versions Pending Migrations
     */
    public static function get_pending()
    {
        $pending_migrations = array();
        $schema             = self::get_schema();
        foreach ($schema['migrations'] as $migration) {
            // If the status is empty then we assume it has not been executed yet.
            if (empty($migration->status) && !in_array($migration, $pending_migrations, true)) {
                $pending_migrations[] = $migration;
            }
        }

        if (!empty($pending_migrations)) {
            set_transient('srm_pending_migrations', $pending_migrations);
        } else {
            delete_transient('srm_pending_migrations');
        }

        return $pending_migrations;
    }

    /**
     * Get any Migrations currently in a failed status
     *
     * @return Array $failed_versions Failed Migrations
     */
    public static function get_failures()
    {
        $failed_migrations = array();
        $schema            = self::get_schema();
        foreach ($schema['migrations'] as $migration) {
            if ('FAILED' === $migration->status && !in_array($migration, $failed_migrations, true)) {
                $failed_migrations[] = $migration;
            }
        }

        if (!empty($failed_migrations)) {
            set_transient('srm_failed_migrations', $failed_migrations);
        } else {
            delete_transient('srm_failed_migrations');
        }

        return $failed_migrations;
    }
}

if (!class_exists('Migrations', true)) {
    return new Migrations();
}
