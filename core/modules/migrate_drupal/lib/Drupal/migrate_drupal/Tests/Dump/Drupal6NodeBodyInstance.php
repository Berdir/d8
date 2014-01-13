<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6NodeBodyInstance.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing field.instance.node.*.body.yml migration.
 */
class Drupal6NodeBodyInstance {

  /**
   * Sample database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('node_type', array(
      'fields' => array(
        'type' => array(
          'description' => 'The machine-readable name of this type.',
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
        ),
        'name' => array(
          'description' => 'The human-readable name of this type.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'module' => array(
          'description' => 'The base string used to construct callbacks corresponding to this node type.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
        'description' => array(
          'description' => 'A brief description of this type.',
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'medium',
        ),
        'help' => array(
          'description' => 'Help information shown to the user when creating a {node} of this type.',
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'medium',
        ),
        'has_title' => array(
          'description' => 'Boolean indicating whether this type uses the {node}.title field.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'tiny',
        ),
        'title_label' => array(
          'description' => 'The label displayed for the title field on the edit form.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'has_body' => array(
          'description' => 'Boolean indicating whether this type uses the {node_revisions}.body field.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'tiny',
        ),
        'body_label' => array(
          'description' => 'The label displayed for the body field on the edit form.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'min_word_count' => array(
          'description' => 'The minimum number of words the body must contain.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'small',
        ),
        'custom' => array(
          'description' => 'A boolean indicating whether this type is defined by a module (FALSE) or by a user via a module like the Content Construction Kit (TRUE).',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'modified' => array(
          'description' => 'A boolean indicating whether this type has been modified by an administrator; currently not used in any way.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'locked' => array(
          'description' => 'A boolean indicating whether the administrator can change the machine name of this type.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'orig_type' => array(
          'description' => 'The original machine-readable name of this node type. This may be different from the current type name if the locked field is 0.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
      ),
    ));
    $database->insert('node_type')->fields(
      array(
        'type',
        'name',
        'module',
        'description',
        'help',
        'has_title',
        'title_label',
        'has_body',
        'body_label',
        'min_word_count',
        'custom',
        'modified',
        'locked',
        'orig_type'
      ))
      ->values(
        array(
          'type' => 'company',
          'name' => 'Company',
          'module' => 'node',
          'description' => 'Company node type',
          'help' => '',
          'has_title' => 1,
          'title_label' => 'Name',
          'has_body' => 1,
          'body_label' => 'Description',
          'min_word_count' => 20,
          'custom' => 0,
          'modified' => 0,
          'locked' => 0,
          'orig_type' => 'company',
      ))
      ->values(array(
          'type' => 'employee',
          'name' => 'Employee',
          'module' => 'node',
          'description' => 'Employee node type',
          'help' => '',
          'has_title' => 1,
          'title_label' => 'Name',
          'has_body' => 1,
          'body_label' => 'Bio',
          'min_word_count' => 20,
          'custom' => 0,
          'modified' => 0,
          'locked' => 0,
          'orig_type' => 'employee',
        ))
      ->values(array(
        'type' => 'sponsor',
        'name' => 'Sponsor',
        'module' => 'node',
        'description' => 'Sponsor node type',
        'help' => '',
        'has_title' => 1,
        'title_label' => 'Name',
        'has_body' => 0,
        'body_label' => 'Body',
        'min_word_count' => 0,
        'custom' => 0,
        'modified' => 0,
        'locked' => 0,
        'orig_type' => '',
      ))
    ->execute();
  }

}
