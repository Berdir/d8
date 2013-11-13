<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Drupal6AggregatorSettings.
 */

namespace Drupal\migrate\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing aggregator.settings.yml migration.
 */
class Drupal6AggregatorSettings {

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
      'module' => 'aggregator',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'aggregator_fetcher',
      'value' => 's:10:"aggregator";',
    ))
    ->values(array(
      'name' => 'aggregator_parser',
      'value' => 's:10:"aggregator";',
    ))
    ->values(array(
      'name' => 'aggregator_processors',
      'value' => 'a:1:{i:0;s:10:"aggregator";}',
    ))
    ->values(array(
      'name' => 'aggregator_allowed_html_tags',
      'value' => 's:70:"<a> <b> <br /> <dd> <dl> <dt> <em> <i> <li> <ol> <p> <strong> <u> <ul>";',
    ))
    ->values(array(
      'name' => 'aggregator_teaser_length',
      'value' => 's:3:"600";',
    ))
    ->values(array(
      'name' => 'aggregator_clear',
      'value' => 's:7:"9676800";',
    ))
    ->values(array(
      'name' => 'aggregator_summary_items',
      'value' => 's:1:"3";',
    ))
    ->values(array(
      'name' => 'aggregator_category_selector',
      'value' => 's:10:"checkboxes";',
    ))
    ->execute();
  }
}
