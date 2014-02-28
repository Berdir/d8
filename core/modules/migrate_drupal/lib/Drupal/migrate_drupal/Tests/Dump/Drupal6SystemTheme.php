<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SystemTheme.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing system.theme.yml migration.
 */
class Drupal6SystemTheme extends Drupal6DumpBase {

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
      'name' => 'admin_theme',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'theme_default',
      'value' => 's:7:"garland";',
    ))
    ->execute();
  }

}
