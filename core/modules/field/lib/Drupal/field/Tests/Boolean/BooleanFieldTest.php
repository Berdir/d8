<?php

/**
 * @file
 * Contains \Drupal\field\Tests\Boolean\BooleanFieldTest.
 */

namespace Drupal\field\Tests\Boolean;

use Drupal\simpletest\WebTestBase;

/**
 * Tests boolean field functionality.
 */
class BooleanFieldTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'entity_test', 'field_ui');

  /**
   * A field to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * The instance used in this test class.
   *
   * @var \Drupal\field\Entity\FieldInstanceConfig
   */
  protected $instance;

  public static function getInfo() {
    return array(
      'name'  => 'Boolean field',
      'description'  => 'Tests boolean field functionality.',
      'group' => 'Field types',
    );
  }

  function setUp() {
    parent::setUp();

    $this->web_user = $this->drupalCreateUser(array(
      'view test entity',
      'administer entity_test content',
      'administer content types',
    ));
    $this->drupalLogin($this->web_user);
  }

  /**
   * Tests boolean field.
   */
  function testBooleanField() {
    $on = $this->randomName();
    $off = $this->randomName();

    // Create a field with settings to validate.
    $field_name = drupal_strtolower($this->randomName());
    $this->field = entity_create('field_config', array(
      'name' => $field_name,
      'entity_type' => 'entity_test',
      'type' => 'boolean',
      'settings' => array(
        'allowed_values' => array(
          1 => $on,
          0 => $off,
        ),
      ),
    ));
    $this->field->save();
    $this->instance = entity_create('field_instance_config', array(
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ));
    $this->instance->save();

    // Create a form display for the default form mode.
    entity_get_form_display('entity_test', 'entity_test', 'default')
      ->setComponent($field_name, array(
        'type' => 'boolean',
      ))
      ->save();
    // Create a display for the full view mode.
    entity_get_display('entity_test', 'entity_test', 'full')
      ->setComponent($field_name, array(
        'type' => 'number_unformatted',
      ))
      ->save();

    // Display creation form.
    $this->drupalGet('entity_test/add');
    $this->assertFieldByName("{$field_name}[value]", '', 'Widget found.');
    $this->assertRaw($on);
    $this->assertRaw($off);

    // Submit and ensure it is accepted.
    $edit = array(
      'user_id' => 1,
      'name' => $this->randomName(),
      "{$field_name}[value]" => 1,
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    preg_match('|entity_test/manage/(\d+)|', $this->url, $match);
    $id = $match[1];
    $this->assertText(t('entity_test @id has been created.', array('@id' => $id)));

    // Verify that boolean value is displayed.
    $entity = entity_load('entity_test', $id);
    $display = entity_get_display($entity->getEntityTypeId(), $entity->bundle(), 'full');
    $content = $display->build($entity);
    $this->drupalSetContent(drupal_render($content));
    $this->assertRaw('<div class="field-item">1</div>');
  }

}
