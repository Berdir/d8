<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Drupal6SystemFile.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing system.file.yml migration.
 */
class Drupal6SystemFile {

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
      'module' => 'system',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'file_directory_path',
      'value' => 's:10:"files/test";',
    ))
    ->values(array(
      'name' => 'file_directory_temp',
      'value' => 's:10:"files/temp";',
    ))
    ->execute();
  }

}
