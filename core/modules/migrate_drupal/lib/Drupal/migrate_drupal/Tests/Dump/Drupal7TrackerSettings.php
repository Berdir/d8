<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal7TrackerSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing tracker.settings.yml migration.
 */
class Drupal7TrackerSettings {

  /**
   * Sample database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public static function load(Connection $database) {
    Drupal6DumpCommon::createVariable($database);
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'cron_index_limit',
      'value' => "i:1000;",
    ))
    ->execute();
  }
}
