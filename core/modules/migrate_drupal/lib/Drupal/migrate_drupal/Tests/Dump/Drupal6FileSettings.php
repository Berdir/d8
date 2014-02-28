<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6FileSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing file.settings.yml migration.
 */
class Drupal6FileSettings extends Drupal6DumpBase {

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
