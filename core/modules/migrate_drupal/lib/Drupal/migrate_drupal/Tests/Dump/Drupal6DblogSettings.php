<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6DblogSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing dblog.settings.yml migration.
 */
class Drupal6DblogSettings extends Drupal6DumpBase {

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
      'name' => 'dblog_row_limit',
      'value' => 'i:1000;',
    ))
    ->execute();
  }
}
