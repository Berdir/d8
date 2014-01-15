<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6Block.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

class Drupal6Block {

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('blocks', array(
      'fields' => array(
        'bid' => array(
          'type' => 'serial',
          'not null' => TRUE,
        ),
        'module' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
        ),
        'delta' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '0',
        ),
        'theme' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
        ),
        'status' => array(
          'type' => 'int',
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
        'region' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
        ),
        'custom' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'throttle' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'visibility' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'pages' => array(
          'type' => 'text',
          'not null' => TRUE,
        ),
        'title' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
        ),
        'cache' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 1,
          'size' => 'tiny',
        ),
      ),
      'primary key' => array(
        'bid',
      ),
      'unique keys' => array(
        'tmd' => array(
          'theme',
          'module',
          'delta',
        ),
      ),
      'indexes' => array(
        'list' => array(
          'theme',
          'status',
          'region',
          'weight',
          'module',
        ),
      ),
    ));
    $database->schema()->createTable('blocks_roles', array(
      'fields' => array(
        'module' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
        ),
        'delta' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
        ),
        'rid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
      ),
      'primary key' => array(
        'module',
        'delta',
        'rid',
      ),
      'indexes' => array(
        'rid' => array(
          'rid',
        ),
      ),
      'module' => 'block',
      'name' => 'blocks_roles',
    ));
    $database->insert('blocks')->fields(array(
      'bid',
      'module',
      'delta',
      'theme',
      'status',
      'weight',
      'region',
      'custom',
      'throttle',
      'visibility',
      'pages',
      'title',
      'cache',
    ))
    ->values(array(
      'bid' => '1',
      'module' => 'user',
      'delta' => '0',
      'theme' => 'garland',
      'status' => '1',
      'weight' => '0',
      'region' => 'left',
      'custom' => '0',
      'throttle' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))
    ->values(array(
      'bid' => '2',
      'module' => 'user',
      'delta' => '1',
      'theme' => 'garland',
      'status' => '1',
      'weight' => '0',
      'region' => 'left',
      'custom' => '0',
      'throttle' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))
    ->values(array(
      'bid' => '3',
      'module' => 'system',
      'delta' => '0',
      'theme' => 'garland',
      'status' => '1',
      'weight' => '10',
      'region' => 'footer',
      'custom' => '0',
      'throttle' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))
    ->execute();

  }
}
