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
class Drupal7TrackerSettings extends Drupal6DumpBase {

  /**
   * Sample database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

   /**
    * {@inheritdoc}
    */
  public function load() {
    $this->createTable('variable');
    $this->database->insert('variable')->fields(array(
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
