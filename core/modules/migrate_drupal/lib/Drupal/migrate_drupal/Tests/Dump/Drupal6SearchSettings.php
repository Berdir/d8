<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6SearchSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing forum.site.yml migration.
 */
class Drupal6SearchSettings {

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
      'module' => 'forum',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'minimum_word_size',
      'value' => 's:1:"3";',
    ))
    ->values(array(
      'name' => 'overlap_cjk',
      'value' => 'i:1;',
    ))
    ->values(array(
      'name' => 'search_cron_limit',
      'value' => 's:3:"100";',
    ))
    ->values(array(
      'name' => 'search_tag_weights',
      'value' => serialize(array(
        'h1' => 25,
        'h2' => 18,
        'h3' => 15,
        'h4' => 12,
        'h5' => 9,
        'h6' => 6,
        'u' => 3,
        'b' => 3,
        'i' => 3,
        'strong' => 3,
        'em' => 3,
        'a' => 10,
      )),
    ))
    ->values(array(
      'name' => 'search_and_or_limit',
      'value' => 'i:7;',
    ))
    ->execute();
  }
}
