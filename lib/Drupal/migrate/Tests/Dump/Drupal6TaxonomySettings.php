<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6TaxonomySettings.
 */

namespace Drupal\migrate\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing taxonomy.settings.yml migration.
 */
class Drupal6TaxonomySettings {

  /**
   * Mock the database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The mocked database connection.
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
      'module' => 'taxonomy',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'taxonomy_override_selector',
      'value' => 'b:0;',
    ))
    ->values(array(
      'name' => 'taxonomy_terms_per_page_admin',
      'value' => 'i:100;',
    ))
    ->execute();
  }
}
