<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6ForumSettings.
 */

namespace Drupal\migrate\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing forum.site.yml migration.
 */
class Drupal6ForumSettings {

  /**
   * Mock the database schema and values.
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
      'module' => 'forum',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'forum_hot_topic',
      'value' => 's:2:"15";',
    ))
    ->values(array(
      'name' => 'forum_per_page',
      'value' => 's:2:"25";',
    ))
    ->values(array(
      'name' => 'forum_order',
      'value' => 's:1:"1";',
    ))
    ->values(array(
      'name' => 'forum_nav_vocabulary',
      'value' => 's:1:"1";',
    ))
    // 'forum_block_num_active' in D8.
    ->values(array(
      'name' => 'forum_block_num_0',
      'value' => 's:1:"5";',
    ))
    // 'forum_block_num_new' in D8.
    ->values(array(
      'name' => 'forum_block_num_1',
      'value' => 's:1:"5";',
    ))
    ->execute();
  }
}
