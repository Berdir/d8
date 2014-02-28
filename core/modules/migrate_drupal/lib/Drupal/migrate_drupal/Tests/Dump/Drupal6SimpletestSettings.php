<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SimpletestSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing simpletest.settings.yml migration.
 */
class Drupal6SimpletestSettings extends Drupal6DumpBase {

  /**
   * Sample database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

   /**
    * {@inheritdoc}
    */
  public function load() {
    $this->createTable('variable');
    $this->database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'simpletest_clear_results',
      'value' => 'b:1;',
    ))
    ->values(array(
      'name' => 'simpletest_httpauth_method',
      'value' => 'i:1;',
    ))
    ->values(array(
      'name' => 'simpletest_httpauth_password',
      'value' => 'N;',
    ))
    ->values(array(
      'name' => 'simpletest_httpauth_username',
      'value' => 'N;',
    ))
    ->values(array(
      'name' => 'simpletest_verbose',
      'value' => 'b:1;',
    ))
    ->execute();
  }
}
