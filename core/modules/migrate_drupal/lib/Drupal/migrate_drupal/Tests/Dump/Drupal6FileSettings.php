<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6FileSettings.
 */

namespace Drupal\migrate\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing file.settings.yml migration.
 */
class Drupal6FileSettings {

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
      'module' => 'file',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'file_description_type',
        'value' => 's:9:"textfield";',
    ))
    ->values(array(
      'name' => 'file_description_length',
        'value' => 'i:128;',
    ))
    ->values(array(
      'name' => 'file_icon_directory',
      'value' => 's:25:"sites/default/files/icons";',
    ))
    ->execute();
  }
}
