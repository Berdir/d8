<?php

/**
 * @file
 * Contains \Drupal\field\Tests\NestedFormTest.
 */

namespace Drupal\field\Tests;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Tests field elements in nested forms.
 */
class NestedFormTest extends FieldTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field_test', 'entity_test');

  public static function getInfo() {
    return array(
      'name' => 'Nested form',
      'description' => 'Test the support for field elements in nested forms.',
      'group' => 'Field API',
    );
  }

  public function setUp() {
    parent::setUp();

    $web_user = $this->drupalCreateUser(array('view test entity', 'administer entity_test content'));
    $this->drupalLogin($web_user);

    $this->field_single = array(
      'name' => 'field_single',
      'entity_type' => 'entity_test',
      'type' => 'test_field',
    );
    $this->field_unlimited = array(
      'name' => 'field_unlimited',
      'entity_type' => 'entity_test',
      'type' => 'test_field',
      'cardinality' => FieldDefinitionInterface::CARDINALITY_UNLIMITED,
    );

    $this->instance = array(
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'label' => $this->randomName() . '_label',
      'description' => '[site:name]_description',
      'weight' => mt_rand(0, 127),
      'settings' => array(
        'test_instance_setting' => $this->randomName(),
      ),
    );
  }

  /**
   * Tests Field API form integration within a subform.
   */
  function testNestedFieldForm() {
    // Add two instances on the 'entity_test'
    entity_create('field_storage_config', $this->field_single)->save();
    entity_create('field_storage_config', $this->field_unlimited)->save();
    $this->instance['field_name'] = 'field_single';
    $this->instance['label'] = 'Single field';
    entity_create('field_instance_config', $this->instance)->save();
    entity_get_form_display($this->instance['entity_type'], $this->instance['bundle'], 'default')
      ->setComponent($this->instance['field_name'])
      ->save();
    $this->instance['field_name'] = 'field_unlimited';
    $this->instance['label'] = 'Unlimited field';
    entity_create('field_instance_config', $this->instance)->save();
    entity_get_form_display($this->instance['entity_type'], $this->instance['bundle'], 'default')
      ->setComponent($this->instance['field_name'])
      ->save();

    // Create two entities.
    $entity_type = 'entity_test';
    $entity_1 = entity_create($entity_type, array('id' => 1));
    $entity_1->enforceIsNew();
    $entity_1->field_single->value = 0;
    $entity_1->field_unlimited->value = 1;
    $entity_1->save();

    $entity_2 = entity_create($entity_type, array('id' => 2));
    $entity_2->enforceIsNew();
    $entity_2->field_single->value = 10;
    $entity_2->field_unlimited->value = 11;
    $entity_2->save();

    // Display the 'combined form'.
    $this->drupalGet('test-entity/nested/1/2');
    $this->assertFieldByName('field_single[0][value]', 0, 'Entity 1: field_single value appears correctly is the form.');
    $this->assertFieldByName('field_unlimited[0][value]', 1, 'Entity 1: field_unlimited value 0 appears correctly is the form.');
    $this->assertFieldByName('entity_2[field_single][0][value]', 10, 'Entity 2: field_single value appears correctly is the form.');
    $this->assertFieldByName('entity_2[field_unlimited][0][value]', 11, 'Entity 2: field_unlimited value 0 appears correctly is the form.');

    // Submit the form and check that the entities are updated accordingly.
    $edit = array(
      'field_single[0][value]' => 1,
      'field_unlimited[0][value]' => 2,
      'field_unlimited[1][value]' => 3,
      'entity_2[field_single][0][value]' => 11,
      'entity_2[field_unlimited][0][value]' => 12,
      'entity_2[field_unlimited][1][value]' => 13,
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $entity_1 = entity_load($entity_type, 1);
    $entity_2 = entity_load($entity_type, 2);
    $this->assertFieldValues($entity_1, 'field_single', array(1));
    $this->assertFieldValues($entity_1, 'field_unlimited', array(2, 3));
    $this->assertFieldValues($entity_2, 'field_single', array(11));
    $this->assertFieldValues($entity_2, 'field_unlimited', array(12, 13));

    // Submit invalid values and check that errors are reported on the
    // correct widgets.
    $edit = array(
      'field_unlimited[1][value]' => -1,
    );
    $this->drupalPostForm('test-entity/nested/1/2', $edit, t('Save'));
    $this->assertRaw(t('%label does not accept the value -1', array('%label' => 'Unlimited field')), 'Entity 1: the field validation error was reported.');
    $error_field = $this->xpath('//input[@id=:id and contains(@class, "error")]', array(':id' => 'edit-field-unlimited-1-value'));
    $this->assertTrue($error_field, 'Entity 1: the error was flagged on the correct element.');
    $edit = array(
      'entity_2[field_unlimited][1][value]' => -1,
    );
    $this->drupalPostForm('test-entity/nested/1/2', $edit, t('Save'));
    $this->assertRaw(t('%label does not accept the value -1', array('%label' => 'Unlimited field')), 'Entity 2: the field validation error was reported.');
    $error_field = $this->xpath('//input[@id=:id and contains(@class, "error")]', array(':id' => 'edit-entity-2-field-unlimited-1-value'));
    $this->assertTrue($error_field, 'Entity 2: the error was flagged on the correct element.');

    // Test that reordering works on both entities.
    $edit = array(
      'field_unlimited[0][_weight]' => 0,
      'field_unlimited[1][_weight]' => -1,
      'entity_2[field_unlimited][0][_weight]' => 0,
      'entity_2[field_unlimited][1][_weight]' => -1,
    );
    $this->drupalPostForm('test-entity/nested/1/2', $edit, t('Save'));
    $this->assertFieldValues($entity_1, 'field_unlimited', array(3, 2));
    $this->assertFieldValues($entity_2, 'field_unlimited', array(13, 12));

    // Test the 'add more' buttons. Only Ajax submission is tested, because
    // the two 'add more' buttons present in the form have the same #value,
    // which confuses drupalPostForm().
    // 'Add more' button in the first entity:
    $this->drupalGet('test-entity/nested/1/2');
    $this->drupalPostAjaxForm(NULL, array(), 'field_unlimited_add_more');
    $this->assertFieldByName('field_unlimited[0][value]', 3, 'Entity 1: field_unlimited value 0 appears correctly is the form.');
    $this->assertFieldByName('field_unlimited[1][value]', 2, 'Entity 1: field_unlimited value 1 appears correctly is the form.');
    $this->assertFieldByName('field_unlimited[2][value]', '', 'Entity 1: field_unlimited value 2 appears correctly is the form.');
    $this->assertFieldByName('field_unlimited[3][value]', '', 'Entity 1: an empty widget was added for field_unlimited value 3.');
    // 'Add more' button in the first entity (changing field values):
    $edit = array(
      'entity_2[field_unlimited][0][value]' => 13,
      'entity_2[field_unlimited][1][value]' => 14,
      'entity_2[field_unlimited][2][value]' => 15,
    );
    $this->drupalPostAjaxForm(NULL, $edit, 'entity_2_field_unlimited_add_more');
    $this->assertFieldByName('entity_2[field_unlimited][0][value]', 13, 'Entity 2: field_unlimited value 0 appears correctly is the form.');
    $this->assertFieldByName('entity_2[field_unlimited][1][value]', 14, 'Entity 2: field_unlimited value 1 appears correctly is the form.');
    $this->assertFieldByName('entity_2[field_unlimited][2][value]', 15, 'Entity 2: field_unlimited value 2 appears correctly is the form.');
    $this->assertFieldByName('entity_2[field_unlimited][3][value]', '', 'Entity 2: an empty widget was added for field_unlimited value 3.');
    // Save the form and check values are saved correctly.
    $this->drupalPostForm(NULL, array(), t('Save'));
    $this->assertFieldValues($entity_1, 'field_unlimited', array(3, 2));
    $this->assertFieldValues($entity_2, 'field_unlimited', array(13, 14, 15));
  }

}
