<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateFieldWidgetSettingsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of field instances.
 */
class MigrateFieldWidgetSettingsTest extends MigrateDrupalTestBase {

  public static $modules = array(
    'number',
    'email',
    'telephone',
    'link',
    'file',
    'image',
    'datetime',
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Migrate field widget settings to entity.form_display.*.*.default.yml',
      'description' => 'Migrate field widget settings.',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test that migrated view modes can be loaded using D8 API's.
   */
  public function testWidgetSettings() {

    $migration = entity_load('migration', 'd6_field_instance_widget_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test the config can be loaded.
    $form_display = entity_load('entity_form_display', 'node.story.default');
    $this->assertEqual(is_null($form_display), FALSE, "Form display node.story.default loaded with config.");

    // Text field.
    $component = $form_display->getComponent('field_test');
    $expected = array('weight' => 1, 'type' => 'text_textfield');
    $expected['settings'] = array('size' => 60, 'placeholder' => '');
    $this->assertEqual($component, $expected, 'Text field settings are correct.');

    // Integer field.
    $component = $form_display->getComponent('field_test_two');
    $expected['type'] = 'number';
    $expected['weight'] = 2;
    $expected['settings'] = array('placeholder' => '');
    $this->assertEqual($component, $expected, 'Integer field settings are correct.');

    // Float field.
    $component = $form_display->getComponent('field_test_three');
    $expected['weight'] = 3;
    $this->assertEqual($component, $expected, 'Float field settings are correct.');

    // Email field.
    $component = $form_display->getComponent('field_test_email');
    $expected['type'] = 'email_default';
    $expected['weight'] = 4;
    $this->assertEqual($component, $expected, 'Email field settings are correct.');

    // Link field.
    $component = $form_display->getComponent('field_test_link');
    $expected['type'] = 'link_default';
    $expected['weight'] = 5;
    $expected['settings'] = array('placeholder_uri' => '', 'placeholder_title' => '');
    $this->assertEqual($component, $expected, 'Link field settings are correct.');

    // File field.
    $component = $form_display->getComponent('field_test_filefield');
    $expected['type'] = 'file_generic';
    $expected['weight'] = 7;
    $expected['settings'] = array('progress_indicator' => 'bar');
    $this->assertEqual($component, $expected, 'File field settings are correct.');

    // Image field.
    $component = $form_display->getComponent('field_test_imagefield');
    $expected['type'] = 'image_image';
    $expected['weight'] = 8;
    $expected['settings'] = array('progress_indicator' => 'bar', 'preview_image_style' => 'thumbnail');
    $this->assertEqual($component, $expected, 'Image field settings are correct.');

    // Phone field.
    $component = $form_display->getComponent('field_test_phone');
    $expected['type'] = 'telephone_default';
    $expected['weight'] = 9;
    $expected['settings'] = array('placeholder' => '');
    $this->assertEqual($component, $expected, 'Phone field settings are correct.');

    // Date fields.
    $component = $form_display->getComponent('field_test_date');
    $expected['type'] = 'datetime_default';
    $expected['weight'] = 10;
    $expected['settings'] = array();
    $this->assertEqual($component, $expected, 'Date field settings are correct.');

    $component = $form_display->getComponent('field_test_datestamp');
    $expected['weight'] = 11;
    $this->assertEqual($component, $expected, 'Date stamp field settings are correct.');

    $component = $form_display->getComponent('field_test_datetime');
    $expected['weight'] = 12;
    $this->assertEqual($component, $expected, 'Datetime field settings are correct.');

  }

}
