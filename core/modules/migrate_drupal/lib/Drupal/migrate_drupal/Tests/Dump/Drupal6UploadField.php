<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6User.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing the upload migration.
 */
class Drupal6UploadField {

  /**
   * @param \Drupal\Core\Database\Connection $database
   *   The connection object.
   */
  public static function load(Connection $database) {
    Drupal6DumpCommon::createVariable($database);
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'menu_masks',
      'value' => 'a:0:{}',
    ))
    ->execute();
  }

}
