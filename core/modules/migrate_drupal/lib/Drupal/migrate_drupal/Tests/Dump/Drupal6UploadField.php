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

    $database->schema()->createTable('upload', array(
      'fields' => array(
        'fid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'nid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'vid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'description' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'list' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
      ),
      'primary key' => array(
        'vid',
        'fid',
      ),
      'indexes' => array(
        'fid' => array(
          'fid',
        ),
        'nid' => array(
          'nid',
        ),
      ),
      'module' => 'upload',
      'name' => 'upload',
    ));

    $database->insert('upload')->fields(array(
      'fid',
      'nid',
      'vid',
      'description',
      'list',
      'weight',
    ))
    ->values(array(
      'fid' => 1,
      'nid' => 1,
      'vid' => 1,
      'description' => 'Test file.',
      'list' => 0,
      'weight' => 1,
    ))
    ->execute();
  }

}
