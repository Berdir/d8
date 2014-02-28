<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6BookSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing book.settings.yml migration.
 */
class Drupal6BookSettings extends Drupal6DumpBase {

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
      'name' => 'book_allowed_types',
      'value' => 'a:1:{i:0;s:4:"book";}',
    ))
    ->values(array(
      'name' => 'book_block_mode',
      'value' => 's:9:"all pages";',
    ))
    ->values(array(
      'name' => 'book_child_type',
      'value' => 's:4:"book";',
    ))
    ->execute();
  }
}
