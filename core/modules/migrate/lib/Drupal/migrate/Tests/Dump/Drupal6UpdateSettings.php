<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6UpdateSettings.
 */

namespace Drupal\migrate\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing update.settings.yml migration.
 */
class Drupal6UpdateSettings {

  /**
   * Sample database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('variable', array(
      'fields' => array(
        'name' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
        ),
        'value' => array(
          'type' => 'blob',
          'not null' => TRUE,
          'size' => 'big',
          'translatable' => TRUE,
        ),
      ),
      'primary key' => array(
        'name',
      ),
      'module' => 'update',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'update_max_fetch_attempts',
      'value' => 'i:2;',
    ))
    ->values(array(
      'name' => 'update_fetch_url',
      'value' => 's:41:"http://updates.drupal.org/release-history";',
    ))
    ->values(array(
      'name' => 'update_notification_threshold',
      'value' => 's:3:"all";',
    ))
    ->values(array(
      'name' => 'update_notify_emails',
      'value' => 'a:0:{}',
    ))
    ->execute();
  }
}
