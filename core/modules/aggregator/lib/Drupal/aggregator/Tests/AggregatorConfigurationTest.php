<?php

/**
 * @file
 * Definition of Drupal\aggregator\Tests\AggregatorConfigurationTest.
 */

namespace Drupal\aggregator\Tests;

/**
 * Tests functionality of the configuration settings in the Aggregator module.
 */
class AggregatorConfigurationTest extends AggregatorTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Aggregator configuration',
      'description' => 'Test aggregator settings page.',
      'group' => 'Aggregator',
    );
  }

  /**
   * Tests the settings form to ensure the correct default values are used.
   */
  function testSettingsPage() {
    $this->drupalGet('admin/config/services/aggregator/settings');
    // Make sure that test plugins are present.
    $this->assertText(t('Test fetcher'));
    $this->assertText(t('Test parser'));
    $this->assertText(t('Test processor'));

    // Set new values and enable test processor.
    $edit = array(
      'aggregator_allowed_html_tags' => '<a>',
      'aggregator_summary_items' => 10,
      'aggregator_clear' => 3600,
      'aggregator_category_selector' => 'select',
      'aggregator_teaser_length' => 200,
      'aggregator_processors[aggregator_test_processor]' => 'aggregator_test_processor',
    );
    $this->drupalPost('admin/config/services/aggregator/settings', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));

    foreach ($edit as $name => $value) {
      $this->assertFieldByName($name, $value, format_string('"@name" has correct default value.', array('@name' => $name)));
    }

    // Check for our test processor settings form.
    $this->assertText(t('Dummy length setting'));
    // Change its value to ensure that settingsSubmit is called.
    $edit = array(
      'dummy_length' => 100,
    );
    $this->drupalPost('admin/config/services/aggregator/settings', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));
    $this->assertFieldByName('dummy_length', 100, '"dummy_length" has correct default value.');
  }
}
