<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Dump\Drupal6UserRole.
 */

namespace Drupal\migrate_drupal\Tests\Dump;
use Drupal\Core\Database\Connection;

/**
 * Database dump for testing user role migration.
 */
class Drupal6UserRole {

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('permission', array(
      'description' => 'Stores permissions for users.',
      'fields' => array(
        'pid' => array(
          'type' => 'serial',
          'not null' => TRUE,
          'description' => 'Primary Key: Unique permission ID.',
        ),
        'rid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The {role}.rid to which the permissions are assigned.',
        ),
        'perm' => array(
          'type' => 'text',
          'not null' => FALSE,
          'size' => 'big',
          'description' => 'List of permissions being assigned.',
        ),
        'tid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Originally intended for taxonomy-based permissions, but never used.',
        ),
      ),
      'primary key' => array('pid'),
      'indexes' => array('rid' => array('rid')),
    ));
    $database->schema()->createTable('role', array(
      'description' => 'Stores user roles.',
      'fields' => array(
        'rid' => array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'Primary Key: Unique role id.',
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
          'description' => 'Unique role name.',
        ),
      ),
      'unique keys' => array('name' => array('name')),
      'primary key' => array('rid'),
    ));
    $database->insert('permission')->fields(array('pid', 'rid', 'perm'))
      ->values(array('pid' => 1, 'rid' => 1, 'perm' => 'migrate test anonymous permission'))
      ->values(array('pid' => 2, 'rid' => 2, 'perm' => 'migrate test authenticated permission'))
      ->values(array('pid' => 3, 'rid' => 3, 'perm' => 'migrate test role 1 test permission'))
      ->values(array('pid' => 4, 'rid' => 4, 'perm' => 'migrate test role 2 test permission, use PHP for settings, administer contact forms, skip comment approval, edit own blog content, edit any blog content, delete own blog content, delete any blog content, create forum content, delete any forum content, delete own forum content, edit any forum content, edit own forum content, administer nodes'))
      ->execute();
    $database->insert('role')->fields(array('rid', 'name'))
      ->values(array('rid' => 1, 'name' => 'anonymous user'))
      ->values(array('rid' => 2, 'name' => 'authenticated user'))
      ->values(array('rid' => 3, 'name' => 'migrate test role 1'))
      ->values(array('rid' => 4, 'name' => 'migrate test role 2'))
      ->values(array('rid' => 5, 'name' => 'migrate test role 3'))
      ->execute();
  }

}
