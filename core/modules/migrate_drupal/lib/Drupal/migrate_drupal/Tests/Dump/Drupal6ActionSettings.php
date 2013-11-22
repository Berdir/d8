<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6ActionSettings.
 */

namespace Drupal\migrate\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing action.settings.yml migration.
 */
class Drupal6ActionSettings {

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
      'module' => 'action',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'actions_max_stack',
      'value' => 'i:35;',
    ))
    ->execute();
  }

}
