<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6SyslogSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing syslog.settings.yml migration.
 */
class Drupal6SyslogSettings {

  /**
   * Sample database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public static function load(Connection $database) {
    $facility = defined('LOG_LOCAL0') ? LOG_LOCAL0 : LOG_USER;

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
      'module' => 'syslog',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'syslog_facility',
      'value' => serialize($facility),
    ))
    ->values(array(
      'name' => 'syslog_identity',
      'value' => 's:6:"drupal";',
    ))
    ->execute();
  }
}
