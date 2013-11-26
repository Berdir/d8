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
      'value' => 's:6:"Drupal";',
    ))
    ->values(array(
      'name' => 'site_mail',
      'value' => serialize(ini_get('sendmail_from')),
    ))
    ->values(array(
      'name' => 'site_slogan',
      'value' => 's:0:"";',
    ))
    ->values(array(
      'name' => 'site_403',
      'value' => 's:0:"";',
    ))
    ->values(array(
      'name' => 'site_404',
      'value' => 's:0:"";',
    ))
    ->values(array(
      'name' => 'site_frontpage',
      'value' => 's:4:"node";',
    ))
    ->values(array(
      'name' => 'admin_compact_mode',
      'value' => 'b:0;',
    ))
    ->execute();
  }

}
