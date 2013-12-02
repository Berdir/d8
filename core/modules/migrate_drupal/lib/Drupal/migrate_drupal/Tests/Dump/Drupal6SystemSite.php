<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Drupal6SystemSite.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing system.site.yml migration.
 */
class Drupal6SystemSite {

  /**
   * Sample database schema and values.
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
      'module' => 'system',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'site_name',
      'value' => serialize('Drupal'),
    ))
    ->values(array(
      'name' => 'site_mail',
      'value' => serialize('admin@example.com'),
    ))
    ->values(array(
      'name' => 'site_slogan',
      'value' => serialize('Migrate rocks'),
    ))
    ->values(array(
      'name' => 'site_frontpage',
      'value' => serialize('anonymous-hp'),
    ))
    ->values(array(
      'name' => 'site_403',
      'value' => serialize('user'),
    ))
    ->values(array(
      'name' => 'site_404',
      'value' => serialize('page-not-found'),
    ))
    ->values(array(
      'name' => 'admin_compact_mode',
      'value' => serialize(FALSE),
    ))
    ->execute();
  }
}
