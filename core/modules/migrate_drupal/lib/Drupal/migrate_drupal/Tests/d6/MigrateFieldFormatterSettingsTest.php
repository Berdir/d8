<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\d6\MigrateFieldInstanceEntityDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test formatter settings to display modes.
 */
class MigrateFieldFormatterSettingsTest extends MigrateDrupalTestBase {

  static $modules = array('node');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate field formatter settings to entity.display.*.*.yml',
      'description'  => 'Upgrade field formatter settings to entity.display.*.*.yml',
      'group' => 'Migrate Drupal',
    );
  }


  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_view_modes' => array(
        array(array(1), array('node', 'preview')),
        array(array(4), array('node', 'rss')),
        array(array('teaser'), array('node', 'teaser')),
        array(array('full'), array('node', 'full')),
      ),
      'd6_field_instance' => array(
        array(array('fieldname', 'page'), array('node', 'fieldname', 'page')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    $migration = entity_load('migration', 'd6_field_formatter_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Test that migrated entity display settings can be loaded using D8 API's.
   */
  public function testEntityDisplaySettings() {
    // Run tests.
    $field_name = "field_test";
    $expected = array(
      'weight' => 1,
      'label' => 'above',
      'type' => 'text_trimmed',
      'settings' => array('trim_length' => 600),
    );

    // Make sure we don't have the excluded print entity display.
    $display = entity_load('entity_view_display', 'node.story.print');
    $this->assertNull($display, "Print entity display not found.");
    // Can we load any entity display.
    $display = entity_load('entity_view_display', 'node.story.teaser');
    $this->assertEqual($display->getComponent($field_name), $expected);

    // Test migrate worked with multiple bundles.
    $display = entity_load('entity_view_display', 'node.article.teaser');
    $this->assertEqual($display->getComponent($field_name), $expected);

    // Test RSS because that has been converted from 4 to rss.
    $display = entity_load('entity_view_display', 'node.story.rss');
    $this->assertEqual($display->getComponent($field_name), $expected);

    // Test the full format with text_default which comes from a static map.
    $expected['type'] = 'text_default';
    $expected['settings'] = array();
    $display = entity_load('entity_view_display', 'node.story.full');
    $this->assertEqual($display->getComponent($field_name), $expected);

    // Check that we can migrate multiple fields.
    $content = $display->get('content');
    $this->assertTrue(isset($content['field_test']), 'Settings for field_test exist.');
    $this->assertTrue(isset($content['field_test_two']), "Settings for field_test_two exist.");

    // Test the number field formatter settings are correct.
    $expected['weight'] = 2;
    $expected['type'] = 'integer';
    $expected['settings'] = array(
      'scale' => 0,
      'decimal_separator' => '.',
      'thousand_separator' => ',',
      'prefix_suffix' => TRUE,
    );
    $component = $display->getComponent('field_test_two');
    $this->assertEqual($component, $expected);
    $expected['weight'] = 3;
    $expected['type'] = 'number_decimal';
    $expected['settings']['scale'] = 2;
    $component = $display->getComponent('field_test_three');
    $this->assertEqual($component, $expected);

    // Test the email field formatter settings are correct.
    $expected['weight'] = 4;
    $expected['type'] = 'email_mailto';
    $expected['settings'] = array();
    $component = $display->getComponent('field_test_email');
    $this->assertEqual($component, $expected);

    // Test the link field formatter settings.
    $expected['weight'] = 5;
    $expected['type'] = 'link';
    $expected['settings'] = array(
      'trim_length' => 80,
      'url_only' => 1,
      'url_plain' => 1,
      'rel' => 0,
      'target' => 0,
    );
    $component = $display->getComponent('field_test_link');
    $this->assertEqual($component, $expected, "node.story.full field_test_link has correct absolute link settings.");
    $expected['settings']['url_only'] = 0;
    $expected['settings']['url_plain'] = 0;
    $display = entity_load('entity_view_display', 'node.story.teaser');
    $component = $display->getComponent('field_test_link');
    $this->assertEqual($component, $expected, "node.story.teaser field_test_link has correct default link settings.");

    // Test the file field formatter settings.
    $expected['weight'] = 7;
    $expected['type'] = 'file_default';
    $expected['settings'] = array();
    $component = $display->getComponent('field_test_filefield');
    $this->assertEqual($component, $expected, "node.story.teaser field_test_filefield is of type file_default.");
    $display = entity_load('entity_view_display', 'node.story.full');
    $expected['type'] = 'file_url_plain';
    $component = $display->getComponent('field_test_filefield');
    $this->assertEqual($component, $expected, "node.story.full field_test_filefield is of type file_url_plain.");

    // Test the image field formatter settings.
    $expected['weight'] = 8;
    $expected['type'] = 'image';
    $expected['settings'] = array('image_style' => '', 'image_link' => '');
    $component = $display->getComponent('field_test_imagefield');
    $this->assertEqual($component, $expected, "node.story.full field_test_imagefield is of type image with the correct settings.");
    $display = entity_load('entity_view_display', 'node.story.teaser');
    $expected['settings']['image_link'] = 'file';
    $component = $display->getComponent('field_test_imagefield');
    $this->assertEqual($component, $expected, "node.story.teaser field_test_imagefield is of type image with the correct settings.");

    // Test phone field.
    $expected['weight'] = 9;
    $expected['type'] = 'text_plain';
    $expected['settings'] = array();
    $component = $display->getComponent('field_test_phone');
    $this->assertEqual($component, $expected, "node.story.teaser field_test_phone is of type text_plain.");

    // Test date field.
    $expected['weight'] = 10;
    $expected['type'] = 'date_default';
    $expected['settings'] = array('format' => 'fallback');
    $component = $display->getComponent('field_test_date');
    $this->assertEqual($component, $expected, "node.story.teaser field_test_date is of type date_default.");
    $display = entity_load('entity_view_display', 'node.story.full');
    $expected['settings']['format'] = 'long';
    $component = $display->getComponent('field_test_date');
    $this->assertEqual($component, $expected, "node.story.full field_test_date is of type date_default with correct settings.");

    // Test date stamp field.
    $expected['weight'] = 11;
    $expected['settings']['format'] = 'fallback';
    $component = $display->getComponent('field_test_datestamp');
    $this->assertEqual($component, $expected, "node.story.full field_test_datestamp is of type date_default with correct settings.");
    $display = entity_load('entity_view_display', 'node.story.teaser');
    $expected['settings'] = array('format' => 'medium');
    $component = $display->getComponent('field_test_datestamp');
    $this->assertEqual($component, $expected, "node.story.teaser field_test_datestamp is of type date_default.");

    // Test datetime field.
    $expected['weight'] = 12;
    $expected['settings'] = array('format' => 'short');
    $component = $display->getComponent('field_test_datetime');
    $this->assertEqual($component, $expected, "node.story.teaser field_test_datetime is of type date_default.");
    $display = entity_load('entity_view_display', 'node.story.full');
    $expected['settings']['format'] = 'fallback';
    $component = $display->getComponent('field_test_datetime');
    $this->assertEqual($component, $expected, "node.story.full field_test_datetime is of type date_default with correct settings.");

    // Test a date field with a random format which should be mapped
    // to date_default.
    $display = entity_load('entity_view_display', 'node.story.rss');
    $expected['settings']['format'] = 'fallback';
    $component = $display->getComponent('field_test_datetime');
    $this->assertEqual($component, $expected, "node.story.full field_test_datetime is of type date_default with correct settings.");
    // Test that our Id map has the correct data.
    $this->assertEqual(array('node', 'story', 'teaser', 'field_test'), entity_load('migration', 'd6_field_formatter_settings')->getIdMap()->lookupDestinationID(array('story', 'teaser', 'node', 'field_test')));
  }

}
