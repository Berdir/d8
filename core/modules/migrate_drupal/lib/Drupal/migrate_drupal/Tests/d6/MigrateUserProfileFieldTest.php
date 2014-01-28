<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserProfileFieldTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of user profile fields.
 */
class MigrateUserProfileFieldTest extends MigrateDrupalTestBase {

  static $modules = array('link', 'options');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate user profile fields',
      'description'  => 'Test the user profile field migration.',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests migration of user profile fields.
   */
  public function testUserProfileFields() {
    $migration = entity_load('migration', 'd6_user_profile_field');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserProfileFields.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Migrated a text field.
    $field = entity_load('field_entity', 'user.profile_color');
    $this->assertEqual($field->type, 'text', 'Field type is text.');
    $this->assertEqual($field->cardinality, 1, 'Text field has correct cardinality');

    // Migrated a textarea.
    $field = entity_load('field_entity', 'user.profile_biography');
    $this->assertEqual($field->type, 'text_long', 'Field type is text_long.');

    // Migrated checkbox field.
    $field = entity_load('field_entity', 'user.profile_sell_address');
    $this->assertEqual($field->type, 'list_integer', 'Field type is list_integer.');

    // Migrated selection field.
    $field = entity_load('field_entity', 'user.profile_sold_to');
    $this->assertEqual($field->type, 'list_text', 'Field type is list_text.');

    // Migrated list field.
    $field = entity_load('field_entity', 'user.profile_bands');
    $this->assertEqual($field->type, 'text', 'Field type is text.');
    $this->assertEqual($field->cardinality, -1, 'List field has correct cardinality');

    // Migrated URL field.
    $field = entity_load('field_entity', 'user.profile_blog');
    $this->assertEqual($field->type, 'link', 'Field type is link.');

    // Migrated date field.
    $field = entity_load('field_entity', 'user.profile_birthdate');
    $this->assertEqual($field->type, 'date', 'Field type is date.');
  }

}
