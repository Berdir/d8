<?php

/**
 * @file
 * Contains \Drupal\datetime\Tests\DatetimeFieldTest.
 */

namespace Drupal\datetime\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Tests Datetime field functionality.
 */
class DatetimeFieldTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'field_test', 'datetime', 'field_ui');

  /**
   * A field to use in this test class.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  protected $field;

  public static function getInfo() {
    return array(
      'name'  => 'Datetime Field',
      'description'  => 'Tests datetime field functionality.',
      'group' => 'Datetime',
    );
  }

  function setUp() {
    parent::setUp();

    $web_user = $this->drupalCreateUser(array(
      'access field_test content',
      'administer field_test content',
      'administer content types',
    ));
    $this->drupalLogin($web_user);

    // Create a field with settings to validate.
    $this->field = field_create_field(array(
      'field_name' => drupal_strtolower($this->randomName()),
      'type' => 'datetime',
      'settings' => array('datetime_type' => 'date'),
    ));
    $this->instance = field_create_instance(array(
      'field_name' => $this->field['field_name'],
      'entity_type' => 'test_entity',
      'bundle' => 'test_bundle',
      'widget' => array(
        'type' => 'datetime_default',
      ),
      'settings' => array(
        'default_value' => 'blank',
      ),
    ));

    $this->display_options = array(
      'type' => 'datetime_default',
      'label' => 'hidden',
      'settings' => array('format_type' => 'medium'),
    );
    entity_get_display($this->instance['entity_type'], $this->instance['bundle'], 'full')
      ->setComponent($this->field['field_name'], $this->display_options)
      ->save();
  }

  /**
   * Tests date field functionality.
   */
  function testDateField() {

    // Display creation form.
    $this->drupalGet('test-entity/add/test_bundle');
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][date]", '', 'Date element found.');
    $this->assertNoFieldByName("{$this->field['field_name']}[$langcode][0][value][time]", '', 'Time element not found.');

    // Submit a valid date and ensure it is accepted.
    $value = '2012-12-31 00:00:00';
    $date = new DrupalDateTime($value);
    $format_type = $date->canUseIntl() ? DrupalDateTime::INTL : DrupalDateTime::PHP;
    $date_format = config('system.date')->get('formats.html_date.pattern.' . $format_type);
    $time_format = config('system.date')->get('formats.html_time.pattern.' . $format_type);

    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date->format($date_format),
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    preg_match('|test-entity/manage/(\d+)/edit|', $this->url, $match);
    $id = $match[1];
    $this->assertRaw(t('test_entity @id has been created.', array('@id' => $id)));
    $this->assertRaw($date->format($date_format));
    $this->assertNoRaw($date->format($time_format));

    // The expected values will use the default time.
    datetime_date_default_time($date);

    // Verify that the date is output according to the formatter settings.
    $options = array(
      'format_type' => array('short', 'medium', 'long'),
    );
    foreach ($options as $setting => $values) {
      foreach ($values as $new_value) {
        // Update the entity display settings.
        $this->display_options['settings'] = array($setting => $new_value);
        $display = entity_get_display($this->instance['entity_type'], $this->instance['bundle'], 'full')
          ->setComponent($this->instance['field_name'], $this->display_options)
          ->save();

        $this->renderTestEntity($id);
        switch ($setting) {
          case 'format_type':
            // Verify that a date is displayed.
            $expected = format_date($date->getTimestamp(), $new_value);
            $this->renderTestEntity($id);
            $this->assertText($expected, format_string('Formatted date field using %value format displayed as %expected.', array('%value' => $new_value, '%expected' => $expected)));
            break;
        }
      }
    }

    // Verify that the plain formatter works.
    $this->display_options['type'] = 'datetime_plain';
    $display = entity_get_display($this->instance['entity_type'], $this->instance['bundle'], 'full')
      ->setComponent($this->instance['field_name'], $this->display_options)
      ->save();
    $expected = $date->format(DATETIME_DATE_STORAGE_FORMAT);
    $this->renderTestEntity($id);
    $this->assertText($expected, format_string('Formatted date field using plain format displayed as %expected.', array('%expected' => $expected)));
  }

  /**
   * Tests date and time field.
   */
  function testDatetimeField() {

    // Change the field to a datetime field.
    $this->field['settings']['datetime_type'] = 'datetime';
    field_update_field($this->field);

    // Display creation form.
    $this->drupalGet('test-entity/add/test_bundle');
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][date]", '', 'Date element found.');
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][time]", '', 'Time element found.');

    // Submit a valid date and ensure it is accepted.
    $value = '2012-12-31 00:00:00';
    $date = new DrupalDateTime($value);
    $format_type = $date->canUseIntl() ? DrupalDateTime::INTL : DrupalDateTime::PHP;
    $date_format = config('system.date')->get('formats.html_date.pattern.' . $format_type);
    $time_format = config('system.date')->get('formats.html_time.pattern.' . $format_type);

    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date->format($date_format),
      "{$this->field['field_name']}[$langcode][0][value][time]" => $date->format($time_format),
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    preg_match('|test-entity/manage/(\d+)/edit|', $this->url, $match);
    $id = $match[1];
    $this->assertRaw(t('test_entity @id has been created.', array('@id' => $id)));
    $this->assertRaw($date->format($date_format));
    $this->assertRaw($date->format($time_format));

    // Verify that the date is output according to the formatter settings.
    $options = array(
      'format_type' => array('short', 'medium', 'long'),
    );
    foreach ($options as $setting => $values) {
      foreach ($values as $new_value) {
        // Update the entity display settings.
        $this->display_options['settings'] = array($setting => $new_value);
        $display = entity_get_display($this->instance['entity_type'], $this->instance['bundle'], 'full')
          ->setComponent($this->instance['field_name'], $this->display_options)
          ->save();

        $this->renderTestEntity($id);
        switch ($setting) {
          case 'format_type':
            // Verify that a date is displayed.
            $expected = format_date($date->getTimestamp(), $new_value);
            $this->renderTestEntity($id);
            $this->assertText($expected, format_string('Formatted date field using %value format displayed as %expected.', array('%value' => $new_value, '%expected' => $expected)));
            break;
        }
      }
    }

    // Verify that the plain formatter works.
    $this->display_options['type'] = 'datetime_plain';
    $display = entity_get_display($this->instance['entity_type'], $this->instance['bundle'], 'full')
      ->setComponent($this->instance['field_name'], $this->display_options)
      ->save();
    $expected = $date->format(DATETIME_DATETIME_STORAGE_FORMAT);
    $this->renderTestEntity($id);
    $this->assertText($expected, format_string('Formatted date field using plain format displayed as %expected.', array('%expected' => $expected)));
  }

  /**
   * Tests Date List Widget functionality.
   */
  function testDatelistWidget() {

    // Change the field to a datetime field.
    $this->field['settings']['datetime_type'] = 'datetime';
    field_update_field($this->field);

    // Change the widget to a datelist widget.
    $increment = 1;
    $date_order = 'YMD';
    $time_type = '12';

    $this->instance['widget']['type'] = 'datetime_datelist';
    $this->instance['widget']['settings'] = array(
      'increment' => $increment,
      'date_order' => $date_order,
      'time_type' => $time_type,
    );
    field_update_instance($this->instance);
    field_cache_clear();

    // Display creation form.
    $this->drupalGet('test-entity/add/test_bundle');
    $field_name = $this->field['field_name'];
    $langcode = LANGUAGE_NOT_SPECIFIED;

    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-$langcode-0-value-year\"]", NULL, 'Year element found.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-year", '', 'No year selected.');
    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-$langcode-0-value-month\"]", NULL, 'Month element found.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-month", '', 'No month selected.');
    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-$langcode-0-value-day\"]", NULL, 'Day element found.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-day", '', 'No day selected.');
    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-$langcode-0-value-hour\"]", NULL, 'Hour element found.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-hour", '', 'No hour selected.');
    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-$langcode-0-value-minute\"]", NULL, 'Minute element found.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-minute", '', 'No minute selected.');
    $this->assertNoFieldByXPath("//*[@id=\"edit-$field_name-$langcode-0-value-second\"]", NULL, 'Second element not found.');
    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-$langcode-0-value-ampm\"]", NULL, 'AMPM element found.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-ampm", '', 'No ampm selected.');

    // Submit a valid date and ensure it is accepted.
    $date_value = array('year' => 2012, 'month' => 12, 'day' => 31, 'hour' => 5, 'minute' => 15);
    $date = new DrupalDateTime($date_value);

    $edit = array();
    // Add the ampm indicator since we are testing 12 hour time.
    $date_value['ampm'] = 'am';
    foreach ($date_value as $part => $value) {
      $edit["{$this->field['field_name']}[$langcode][0][value][$part]"] = $value;
    }

    $this->drupalPost(NULL, $edit, t('Save'));
    preg_match('|test-entity/manage/(\d+)/edit|', $this->url, $match);
    $id = $match[1];
    $this->assertRaw(t('test_entity @id has been created.', array('@id' => $id)));

    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-year", '2012', 'Correct year selected.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-month", '12', 'Correct month selected.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-day", '31', 'Correct day selected.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-hour", '5', 'Correct hour selected.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-minute", '15', 'Correct minute selected.');
    $this->assertOptionSelected("edit-$field_name-$langcode-0-value-ampm", 'am', 'Correct ampm selected.');
  }

  /**
   * Test default value functionality.
   */
  function testDefaultValue() {

    // Change the field to a datetime field.
    $this->field['settings']['datetime_type'] = 'datetime';
    field_update_field($this->field);

    // Set the default value to 'now'.
    $this->instance['settings']['default_value'] = 'now';
    $this->instance['default_value_function'] = 'datetime_default_value';
    field_update_instance($this->instance);

    // Display creation form.
    $date = new DrupalDateTime();
    $date_format = 'Y-m-d';
    $this->drupalGet('test-entity/add/test_bundle');
    $langcode = LANGUAGE_NOT_SPECIFIED;

    // See if current date is set. We cannot test for the precise time because
    // it may be a few seconds between the time the comparison date is created
    // and the form date, so we just test the date and that the time is not
    // empty.
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][date]", $date->format($date_format), 'Date element found.');
    $this->assertNoFieldByName("{$this->field['field_name']}[$langcode][0][value][time]", '', 'Time element found.');

    // Set the default value to 'blank'.
    $this->instance['settings']['default_value'] = 'blank';
    $this->instance['default_value_function'] = 'datetime_default_value';
    field_update_instance($this->instance);

    // Display creation form.
    $date = new DrupalDateTime();
    $this->drupalGet('test-entity/add/test_bundle');

    // See that no date is set.
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][date]", '', 'Date element found.');
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][time]", '', 'Time element found.');
  }

  /**
   * Test that invalid values are caught and marked as invalid.
   */
  function testInvalidField() {

    // Change the field to a datetime field.
    $this->field['settings']['datetime_type'] = 'datetime';
    field_update_field($this->field);

    // Display creation form.
    $this->drupalGet('test-entity/add/test_bundle');
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][date]", '', 'Date element found.');
    $this->assertFieldByName("{$this->field['field_name']}[$langcode][0][value][time]", '', 'Time element found.');

    // Submit invalid dates and ensure they is not accepted.
    $date_value = '';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => '12:00:00',
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', 'Empty date value has been caught.');

    $date_value = 'aaaa-12-01';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => '00:00:00',
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', format_string('Invalid year value %date has been caught.', array('%date' => $date_value)));

    $date_value = '2012-75-01';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => '00:00:00',
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', format_string('Invalid month value %date has been caught.', array('%date' => $date_value)));

    $date_value = '2012-12-99';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => '00:00:00',
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', format_string('Invalid day value %date has been caught.', array('%date' => $date_value)));

    $date_value = '2012-12-01';
    $time_value = '';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => $time_value,
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', 'Empty time value has been caught.');

    $date_value = '2012-12-01';
    $time_value = '49:00:00';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => $time_value,
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', format_string('Invalid hour value %time has been caught.', array('%time' => $time_value)));

    $date_value = '2012-12-01';
    $time_value = '12:99:00';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => $time_value,
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', format_string('Invalid minute value %time has been caught.', array('%time' => $time_value)));

    $date_value = '2012-12-01';
    $time_value = '12:15:99';
    $edit = array(
      "{$this->field['field_name']}[$langcode][0][value][date]" => $date_value,
      "{$this->field['field_name']}[$langcode][0][value][time]" => $time_value,
    );
    $this->drupalPost(NULL, $edit, t('Save'));
    $this->assertText('date is invalid', format_string('Invalid second value %time has been caught.', array('%time' => $time_value)));
  }

  /**
   * Renders a test_entity and sets the output in the internal browser.
   *
   * @param int $id
   *   The test_entity ID to render.
   * @param string $view_mode
   *   (optional) The view mode to use for rendering. Defaults to 'full'.
   * @param bool $reset
   *   (optional) Whether to reset the test_entity controller cache. Defaults to
   *   TRUE to simplify testing.
   */
  protected function renderTestEntity($id, $view_mode = 'full', $reset = TRUE) {
    if ($reset) {
      entity_get_controller('test_entity')->resetCache(array($id));
    }
    $entity = field_test_entity_test_load($id);
    $display = entity_get_display('test_entity', $entity->bundle(), 'full');
    field_attach_prepare_view('test_entity', array($entity->id() => $entity), array($entity->bundle() => $display), $view_mode);
    $entity->content = field_attach_view($entity, $display, $view_mode);

    $output = drupal_render($entity->content);
    $this->drupalSetContent($output);
    $this->verbose($output);
  }

}
