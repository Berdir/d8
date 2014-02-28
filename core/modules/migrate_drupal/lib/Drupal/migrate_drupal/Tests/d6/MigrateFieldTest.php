<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserRoleTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateFieldTest extends MigrateDrupalTestBase {

  public static $modules = array('number', 'email', 'telephone', 'link', 'file', 'image', 'datetime', 'node');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate fields to field.*.*.yml',
      'description'  => 'Migrate fields',
      'group' => 'Migrate Drupal',
    );
  }

  public function testFields() {
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_field');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Text field.
    $field = entity_load('field_config', 'node.field_test');
    $expected = array('max_length' => 255);
    $this->assertEqual($field->type, "text", "Field type is text.");
    $this->assertEqual($field->status, TRUE, "Status is TRUE");
    $this->assertEqual($field->settings, $expected, "Field type text settings are correct");

    // Integer field.
    $field = entity_load('field_config', 'node.field_test_two');
    $this->assertEqual($field->type, "number_integer", "Field type is number_integer.");

    // Float field.
    $field = entity_load('field_config', 'node.field_test_three');
    $this->assertEqual($field->type, "number_float", "Field type is number_float.");

    // Link field.
    $field = entity_load('field_config', 'node.field_test_link');
    $this->assertEqual($field->type, "link", "Field type is link.");

    // File field.
    $field = entity_load('field_config', 'node.field_test_filefield');
    $this->assertEqual($field->type, "file", "Field type is file.");

    // Image field.
    $field = entity_load('field_config', 'node.field_test_imagefield');
    $expected = array(
      'column_groups' => array(
        'alt' => array('label' => 'Test alt'),
        'title' => array('label' => 'Test title'),
      ),
      'uri_scheme' => 'public',
      'default_image' => array('fid' => '', 'alt' => '', 'title' => '', 'width' => '', 'height' => ''),
    );
    $this->assertEqual($field->type, "image", "Field type is image.");
    $this->assertEqual($field->settings, $expected, "Field type image settings are correct");

    // Phone field.
    $field = entity_load('field_config', 'node.field_test_phone');
    $this->assertEqual($field->type, "telephone", "Field type is telephone.");

    // Date field.
    $field = entity_load('field_config', 'node.field_test_datetime');
    $this->assertEqual($field->type, "datetime", "Field type is datetime.");
    $this->assertEqual($field->status, FALSE, "Status is FALSE");
  }

}
