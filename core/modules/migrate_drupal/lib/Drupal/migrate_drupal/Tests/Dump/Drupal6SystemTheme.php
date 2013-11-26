<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Drupal6SystemTheme.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing system.theme.yml migration.
 */
class Drupal6SystemTheme {

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
      'name' => 'admin_theme',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'theme_default',
      'value' => 's:7:"garland";',
    ))
    ->execute();
  }

}
