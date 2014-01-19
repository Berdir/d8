<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6NodeType.
 */

namespace Drupal\migrate_drupal\Tests\Dump;
use Drupal\Core\Database\Connection;

/**
 * Database dump for testing node type migration.
 */
class Drupal6NodeType {

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('node_type', array(
      'description' => 'Stores information about all defined {node} types.',
      'fields' => array(
        'type' => array(
          'description' => 'The machine-readable name of this type.',
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE),
        'name' => array(
          'description' => 'The human-readable name of this type.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => ''),
        'module' => array(
          'description' => 'The base string used to construct callbacks corresponding to this node type.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE),
        'description'    => array(
          'description' => 'A brief description of this type.',
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'medium'),
        'help' => array(
          'description' => 'Help information shown to the user when creating a {node} of this type.',
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'medium'),
        'has_title' => array(
          'description' => 'Boolean indicating whether this type uses the {node}.title field.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'tiny'),
        'title_label' => array(
          'description' => 'The label displayed for the title field on the edit form.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => ''),
        'has_body' => array(
          'description' => 'Boolean indicating whether this type uses the {node_revisions}.body field.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'tiny'),
        'body_label' => array(
          'description' => 'The label displayed for the body field on the edit form.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => ''),
        'min_word_count' => array(
          'description' => 'The minimum number of words the body must contain.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'small'),
        'custom' => array(
          'description' => 'A boolean indicating whether this type is defined by a module (FALSE) or by a user via a module like the Content Construction Kit (TRUE).',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny'),
        'modified' => array(
          'description' => 'A boolean indicating whether this type has been modified by an administrator; currently not used in any way.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny'),
        'locked' => array(
          'description' => 'A boolean indicating whether the administrator can change the machine name of this type.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny'),
        'orig_type' => array(
          'description' => 'The original machine-readable name of this node type. This may be different from the current type name if the locked field is 0.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
      ),
      'primary key' => array('type'),
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
        'orig_type',
      ))
      ->values(array(
        'type' => 'migrate_test_page',
        'name' => 'Migrate test page',
        'module' => 'node',
        'description' => "A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site's initial home page.",
        'help' => '',
        'has_title' => 1,
        'title_label' => 'Title',
        'has_body' => 1,
        'body_label' => 'Body',
        'min_word_count' => 0,
        'custom' => 1,
        'modified' => 1,
        'locked' => 0,
        'orig_type' => 'page',
      ))
      ->values(array(
        'type' => 'migrate_test_story',
        'name' => 'Migrate test story',
        'module' => 'node',
        'description' => "A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site's initial home page, and provides the ability to post comments.",
        'help' => '',
        'has_title' => 1,
        'title_label' => 'Title',
        'has_body' => 1,
        'body_label' => 'Body',
        'min_word_count' => 0,
        'custom' => 1,
        'modified' => 1,
        'locked' => 0,
        'orig_type' => 'story',
      ))
      ->execute();

    Drupal6DumpCommon::createVariable($database);
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'node_options_migrate_test_page',
      'value' => serialize(array(
        0 => 'status',
        1 => 'promote',
        2 => 'sticky',
      )),
    ))
    ->values(array(
      'name' => 'node_options_migrate_test_story',
      'value' => serialize(array(
        0 => 'status',
        1 => 'promote',
      )),
    ))
    ->values(array(
      'name' => 'theme_settings',
      'value' => serialize(array(
        'toggle_logo' => 1,
        'toggle_name' => 1,
        'toggle_slogan' => 0,
        'toggle_mission' => 1,
        'toggle_node_user_picture' => 0,
        'toggle_comment_user_picture' => 0,
        'toggle_search' => 0,
        'toggle_favicon' => 1,
        'toggle_primary_links' => 1,
        'toggle_secondary_links' => 1,
        'toggle_node_info_test' => 1,
        'toggle_node_info_something' => 1,
        'default_logo' => 1,
        'logo_path' => '',
        'logo_upload' => '',
        'default_favicon' => 1,
        'favicon_path' => '',
        'favicon_upload' => '',
        'toggle_node_info_migrate_test_page' => 1,
        'toggle_node_info_migrate_test_story' => 1,
      )),
    ))
    ->execute();
  }
}
