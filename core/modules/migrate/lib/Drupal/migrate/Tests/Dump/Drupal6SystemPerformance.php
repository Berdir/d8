<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6SystemPerformance.
 */

namespace Drupal\migrate\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing system.performance.yml migration.
 */
class Drupal6SystemPerformance {

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
      'name' => 'preprocess_css',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'preprocess_js',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'cache_lifetime',
      'value' => 'i:0;',
    ))
    ->execute();
  }

}
