<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Tests\EntityPropertyTest.
 */

namespace Drupal\system\Tests\Entity;

use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Property\ItemListInterface;
use Drupal\Core\Entity\Property\ItemInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests Entity API base functionality.
 */
class EntityPropertyTest extends WebTestBase  {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_test');

  public static function getInfo() {
    return array(
      'name' => 'Entity property API',
      'description' => 'Tests the Entity property API',
      'group' => 'Entity API',
    );
  }

  /**
   * Creates a test entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  protected function createTestEntity() {
    $this->entity_name = $this->randomName();
    $this->entity_user = $this->drupalCreateUser();
    $this->entity_field_text = $this->randomName();

    // Pass in the value of the name property when creating. With the user
    // property we test setting a property after creation.
    $entity = entity_create('entity_test', array());
    $entity->user_id->value = $this->entity_user->uid;
    $entity->name->value = $this->entity_name;

    // Set a value for the test field.
    $entity->field_test_text->value = $this->entity_field_text;

    return $entity;
  }

  /**
   * Tests reading and writing properties and property items.
   */
  public function testReadWrite() {
    $entity = $this->createTestEntity();

    // Access the name property.
    $this->assertTrue($entity->name instanceof ItemListInterface, 'Property implements interface');
    $this->assertTrue($entity->name[0] instanceof ItemInterface, 'Property item implements interface');

    $this->assertEqual($this->entity_name, $entity->name->value, 'Name value can be read.');
    $this->assertEqual($this->entity_name, $entity->name[0]->value, 'Name value can be read through list access.');
    $this->assertEqual($entity->name->getValue(), array(0 => array('value' => $this->entity_name)), 'Plain property value returned.');

    // Change the name.
    $new_name = $this->randomName();
    $entity->name->value = $new_name;
    $this->assertEqual($new_name, $entity->name->value, 'Name can be updated and read.');
    $this->assertEqual($entity->name->getValue(), array(0 => array('value' => $new_name)), 'Plain property value reflects the update.');

    $new_name = $this->randomName();
    $entity->name[0]->value = $new_name;
    $this->assertEqual($new_name, $entity->name->value, 'Name can be updated and read through list access.');

    // Access the user property.
    $this->assertTrue($entity->user_id instanceof ItemListInterface, 'Property implements interface');
    $this->assertTrue($entity->user_id[0] instanceof ItemInterface, 'Property item implements interface');

    $this->assertEqual($this->entity_user->uid, $entity->user_id->value, 'User id can be read.');
    $this->assertEqual($this->entity_user->name, $entity->user_id->entity->name, 'User name can be read.');

    // Change the assigned user by entity.
    $new_user = $this->drupalCreateUser();
    $entity->user_id->entity = $new_user;
    $this->assertEqual($new_user->uid, $entity->user_id->value, 'Updated user id can be read.');
    $this->assertEqual($new_user->name, $entity->user_id->entity->name, 'Updated user name value can be read.');

    // Change the assigned user by id.
    $new_user = $this->drupalCreateUser();
    $entity->user_id->value = $new_user->uid;
    $this->assertEqual($new_user->uid, $entity->user_id->value, 'Updated user id can be read.');
    $this->assertEqual($new_user->name, $entity->user_id->entity->name, 'Updated user name value can be read.');

    // Try unsetting a property.
    $entity->name->value = NULL;
    $entity->user_id->value = NULL;
    $this->assertNull($entity->name->value, 'Name property is not set.');
    $this->assertNull($entity->user_id->value, 'User ID property is not set.');
    $this->assertNull($entity->user_id->entity, 'User entity property is not set.');

    // Test using isset(), empty() and unset().
    $entity->name->value = 'test unset';
    unset($entity->name->value);
    $this->assertFalse(isset($entity->name->value), 'Name is not set.');
    $this->assertFalse(isset($entity->name[0]->value), 'Name is not set.');
    $this->assertTrue(empty($entity->name->value), 'Name is empty.');
    $this->assertTrue(empty($entity->name[0]->value), 'Name is empty.');

    $entity->name->value = 'a value';
    $this->assertTrue(isset($entity->name->value), 'Name is set.');
    $this->assertTrue(isset($entity->name[0]->value), 'Name is set.');
    $this->assertFalse(empty($entity->name->value), 'Name is not empty.');
    $this->assertFalse(empty($entity->name[0]->value), 'Name is not empty.');
    $this->assertTrue(isset($entity->name[0]), 'Name string item is set.');
    $this->assertFalse(isset($entity->name[1]), 'Second name string item is not set as it does not exist');
    $this->assertTrue(isset($entity->name), 'Name property is set.');
    $this->assertFalse(isset($entity->nameInvalid), 'Not existing property is not set.');

    unset($entity->name[0]);
    $this->assertFalse(isset($entity->name[0]), 'Name property item is not set.');
    $this->assertFalse(isset($entity->name[0]->value), 'Name is not set.');
    $this->assertFalse(isset($entity->name->value), 'Name is not set.');

    $entity->name->value = 'a value';
    $this->assertTrue(isset($entity->name->value), 'Name is set.');
    unset($entity->name);
    $this->assertFalse(isset($entity->name), 'Name property is not set.');
    $this->assertFalse(isset($entity->name[0]), 'Name property item is not set.');
    $this->assertFalse(isset($entity->name[0]->value), 'Name is not set.');
    $this->assertFalse(isset($entity->name->value), 'Name is not set.');

    // Access the language property.
    $this->assertEqual(LANGUAGE_NOT_SPECIFIED, $entity->langcode->value, 'Language code can be read.');
    $this->assertEqual(language_load(LANGUAGE_NOT_SPECIFIED), $entity->langcode->language, 'Language object can be read.');

    // Change the language by code.
    $entity->langcode->value = language_default()->langcode;
    $this->assertEqual(language_default()->langcode, $entity->langcode->value, 'Language code can be read.');
    $this->assertEqual(language_default(), $entity->langcode->language, 'Language object can be read.');

    // Revert language by code then try setting it by language object.
    $entity->langcode->value = LANGUAGE_NOT_SPECIFIED;
    $entity->langcode->language = language_default();
    $this->assertEqual(language_default()->langcode, $entity->langcode->value, 'Language code can be read.');
    $this->assertEqual(language_default(), $entity->langcode->language, 'Language object can be read.');

    // Access the text field and test updating.
    $this->assertEqual($entity->field_test_text->value, $this->entity_field_text, 'Text field can be read.');
    $new_text = $this->randomName();
    $entity->field_test_text->value = $new_text;
    $this->assertEqual($entity->field_test_text->value, $new_text, 'Updated text field can be read.');

    // Test creating the entity by passing in plain values.
    $this->entity_name = $this->randomName();
    $name_item[0]['value'] = $this->entity_name;
    $this->entity_user = $this->drupalCreateUser();
    $user_item[0]['value'] = $this->entity_user->uid;
    $this->entity_field_text = $this->randomName();
    $text_item[0]['value'] = $this->entity_field_text;

    $entity = entity_create('entity_test', array(
      'name' => $name_item,
      'user_id' => $user_item,
      'field_test_text' => $text_item,
    ));
    $this->assertEqual($this->entity_name, $entity->name->value, 'Name value can be read.');
    $this->assertEqual($this->entity_user->uid, $entity->user_id->value, 'User id can be read.');
    $this->assertEqual($this->entity_user->name, $entity->user_id->entity->name, 'User name can be read.');
    $this->assertEqual($this->entity_field_text, $entity->field_test_text->value, 'Text field can be read.');

    // Test copying property values.
    $entity2 = $this->createTestEntity();
    $entity2->name = $entity->name;
    $entity2->user_id = $entity->user_id;
    $entity2->field_test_text = $entity->field_test_text;

    $this->assertTrue($entity->name !== $entity2->name, 'Copying properties results in a different property wrapper.');
    $this->assertEqual($entity->name->value, $entity2->name->value, 'Name property copied.');
    $this->assertEqual($entity->user_id->value, $entity2->user_id->value, 'User id property copied.');
    $this->assertEqual($entity->field_test_text->value, $entity2->field_test_text->value, 'Text field copied.');

    // Tests adding a value to a property list.
    $entity->name[] = 'Another name';
    $this->assertEqual($entity->name[1]->value == 'Another name', 'List item added via [].');
    $entity->name[2]->value = 'Third name';
    $this->assertEqual($entity->name[2]->value == 'Third name', 'List item added by a accessing not yet created item.');

    // Test removing and empty-ing list items.
    $this->assertEqual(count($entity->name), 3, 'List has 3 items.');
    unset($entity->name[1]);
    $this->assertEqual(count($entity->name), 2, 'Second list item has been removed.');
    $entity->name[2] = NULL;
    $this->assertEqual(count($entity->name), 2, 'Assigning NULL does not reduce array count.');
    $this->assertTrue($entity->name[2]->isEmpty(), 'Assigning NULL empties the item.');

    // Test using isEmpty().
    unset($entity->name[2]);
    $this->assertFalse($entity->name[0]->isEmpty(), 'Name item is not empty.');
    $entity->name->value = NULL;
    $this->assertTrue($entity->name[0]->isEmpty(), 'Name item is empty.');
    $this->assertTrue($entity->name->isEmpty(), 'Name property is empty.');
    $this->assertEqual(count($entity->name), 1, 'Empty item is considered when counting.');
    $this->assertEqual(count(iterator_to_array($entity->name->getIterator())), count($entity->name), 'Count matches iterator count.');
    $this->assertTrue($entity->name->getValue() === array(0 => NULL), 'Name property value contains a NULL value.');
  }

  /**
   * Tries to save and load an entity again.
   */
  public function testSave() {
    $entity = $this->createTestEntity();
    $entity->save();
    $this->assertTrue((bool) $entity->id(), 'Entity has received an id.');

    $entity = entity_load('entity_test', $entity->id());
    $this->assertTrue((bool) $entity->id(), 'Entity loaded.');

    // Access the name property.
    $this->assertEqual(1, $entity->id->value, 'ID value can be read.');
    $this->assertTrue(is_string($entity->uuid->value), 'UUID value can be read.');
    $this->assertEqual(LANGUAGE_NOT_SPECIFIED, $entity->langcode->value, 'Language code can be read.');
    $this->assertEqual(language_load(LANGUAGE_NOT_SPECIFIED), $entity->langcode->language, 'Language object can be read.');
    $this->assertEqual($this->entity_user->uid, $entity->user_id->value, 'User id can be read.');
    $this->assertEqual($this->entity_user->name, $entity->user_id->entity->name, 'User name can be read.');
    $this->assertEqual($this->entity_field_text, $entity->field_test_text->value, 'Text field can be read.');
  }

  /**
   * Tests introspection and getting metadata upfront.
   */
  public function testIntrospection() {
    // Test getting metadata upfront, i.e. without having an entity object.
    $definition = array(
      'type' => 'entity',
      'constraints' => array(
        'entity type' => 'entity_test',
      ),
      'label' => t('Test entity'),
    );
    $wrapped_entity = drupal_wrap_data($definition);
    $definitions = $wrapped_entity->getPropertyDefinitions($definition);
    $this->assertEqual($definitions['name']['type'], 'string_item', 'Name property found.');
    $this->assertEqual($definitions['user_id']['type'], 'entityreference_item', 'User property found.');
    $this->assertEqual($definitions['field_test_text']['type'], 'text_item', 'Test-text-field property found.');

    // Test introspecting an entity object.
    // @todo: Add bundles and test bundles as well.
    $entity = entity_create('entity_test', array());

    $definitions = $entity->getPropertyDefinitions();
    $this->assertEqual($definitions['name']['type'], 'string_item', 'Name property found.');
    $this->assertEqual($definitions['user_id']['type'], 'entityreference_item', 'User property found.');
    $this->assertEqual($definitions['field_test_text']['type'], 'text_item', 'Test-text-field property found.');

    $name_properties = $entity->name->getPropertyDefinitions();
    $this->assertEqual($name_properties['value']['type'], 'string', 'String value property of the name found.');

    $userref_properties = $entity->user_id->getPropertyDefinitions();
    $this->assertEqual($userref_properties['value']['type'], 'integer', 'Entity id property of the user found.');
    $this->assertEqual($userref_properties['entity']['type'], 'entity', 'Entity reference property of the user found.');

    $textfield_properties = $entity->field_test_text->getPropertyDefinitions();
    $this->assertEqual($textfield_properties['value']['type'], 'string', 'String value property of the test-text field found.');
    $this->assertEqual($textfield_properties['format']['type'], 'string', 'String format property of the test-text field found.');
    $this->assertEqual($textfield_properties['processed']['type'], 'string', 'String processed property of the test-text field found.');

    // @todo: Once the user entity has definitions, continue testing getting
    // them from the $userref_values['entity'] property.
  }

  /**
   * Tests iterating over properties.
   */
  public function testIterator() {
    $entity = $this->createTestEntity();

    foreach ($entity as $name => $property) {
      $this->assertTrue($property instanceof ItemListInterface, "Property $name implements interface.");

      foreach ($property as $delta => $item) {
        $this->assertTrue($property[0] instanceof ItemInterface, "Item $delta of property $name implements interface.");

        foreach ($item as $value_name => $value_property) {
          $this->assertTrue($value_property instanceof TypedDataInterface, "Value $value_name of item $delta of property $name implements interface.");

          $value = $value_property->getValue();
          $this->assertTrue(!isset($value) || is_scalar($value) || $value instanceof EntityInterface, "Value $value_name of item $delta of property $name is a primitive or an entity.");
        }
      }
    }

    $properties = $entity->getProperties();
    $this->assertEqual(array_keys($properties), array_keys($entity->getPropertyDefinitions()), 'All properties returned.');
    $this->assertEqual($properties, iterator_to_array($entity->getIterator()), 'Entity iterator iterates over all properties.');
  }

  /**
   * Tests working with entity properties based upon data structure and data
   * list interfaces.
   */
  public function testDataStructureInterfaces() {
    $entity = $this->createTestEntity();
    $entity->save();
    $entity_definition = array(
      'type' => 'entity',
      'constraints' => array(
        'entity type' => 'entity_test',
      ),
      'label' => t('Test entity'),
    );
    $wrapped_entity = drupal_wrap_data($entity_definition, $entity);

    // For the test we navigate through the tree of contained properties and get
    // all contained strings, limited by a certain depth.
    $strings = array();
    $this->getContainedStrings($wrapped_entity, 0, $strings);

    // @todo: Once the user entity has defined properties this should contain
    // the user name and other user entity strings as well.
    $target_strings = array(
      $entity->uuid->value,
      LANGUAGE_NOT_SPECIFIED,
      $this->entity_name,
      $this->entity_field_text,
      // Field format.
      NULL,
    );
    $this->assertEqual($strings, $target_strings, 'All contained strings found.');
  }

  /**
   * Recursive helper for getting all contained strings,
   * i.e. properties of type string.
   */
  public function getContainedStrings(TypedDataInterface $wrapper, $depth, array &$strings) {

    if ($wrapper->getType() == 'string') {
      $strings[] = $wrapper->getValue();
    }

    // Recurse until a certain depth is reached if possible.
    if ($depth < 7) {
      if ($wrapper instanceof \Drupal\Core\TypedData\ListInterface) {
        foreach ($wrapper as $item) {
          $this->getContainedStrings($item, $depth + 1, $strings);
        }
      }
      elseif ($wrapper instanceof \Drupal\Core\TypedData\ComplexDataInterface) {
        foreach ($wrapper as $name => $property) {
          $this->getContainedStrings($property, $depth + 1, $strings);
        }
      }
    }
  }

  /**
   * Tests getting processed property values via a computed property.
   */
  public function testComputedProperties() {
    // Make the test text field processed.
    $instance = field_info_instance('entity_test', 'field_test_text', 'entity_test');
    $instance['settings']['text_processing'] = 1;
    field_update_instance($instance);

    $entity = $this->createTestEntity();
    $entity->field_test_text->value = "The <strong>text</strong> text to filter.";
    $entity->field_test_text->format = filter_default_format();

    $target = "<p>The &lt;strong&gt;text&lt;/strong&gt; text to filter.</p>\n";
    $this->assertEqual($entity->field_test_text->processed, $target, 'Text is processed with the default filter.');

    // Save and load entity and make sure it still works.
    $entity->save();
    $entity = entity_load('entity_test', $entity->id());
    $this->assertEqual($entity->field_test_text->processed, $target, 'Text is processed with the default filter.');
  }
}
