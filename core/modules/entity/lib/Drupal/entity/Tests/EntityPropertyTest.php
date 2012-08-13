<?php

/**
 * @file
 * Definition of Drupal\entity\Tests\EntityPropertyTest.
 */

namespace Drupal\entity\Tests;

use Drupal\entity\EntityPropertyInterface;
use Drupal\entity\EntityPropertyItemInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests Entity API base functionality.
 */
class EntityPropertyTest extends WebTestBase  {

  public static function getInfo() {
    return array(
      'name' => 'Entity property API',
      'description' => 'Verifies the Entity property API',
      'group' => 'Entity API',
    );
  }

  public function setUp() {
    parent::setUp(array('entity_test'));
  }

  /**
   * Creates a test entity.
   *
   * @return \Drupal\entity\EntityInterface
   */
  protected function createTestEntity() {
    $this->entity_name = $this->randomName();
    $name_property[0]['value'] = $this->entity_name;
    $this->entity_user = $this->drupalCreateUser();

    // Pass in the value of the name property when creating. With the user
    // property we test setting a property after creation.
    $entity = entity_create('entity_test', array('name' => $name_property));
    $entity->user->id = $this->entity_user->uid;
    return $entity;
  }

  /**
   * Tests reading and writing properties and property items.
   */
  public function testReadWrite() {
    $entity = $this->createTestEntity();

    // Access the name property.
    $this->assertTrue($entity->name instanceof EntityPropertyInterface, 'Property implements interface');
    $this->assertTrue($entity->name[0] instanceof EntityPropertyItemInterface, 'Property item implements interface');

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
    $this->assertTrue($entity->user instanceof EntityPropertyInterface, 'Property implements interface');
    $this->assertTrue($entity->user[0] instanceof EntityPropertyItemInterface, 'Property item implements interface');

    $this->assertEqual($this->entity_user->uid, $entity->user->id, 'User id can be read.');
    $this->assertEqual($this->entity_user->name, $entity->user->entity->name, 'User name can be read.');

    // Change the assigned user by entity.
    $new_user = $this->drupalCreateUser();
    $entity->user->entity = $new_user;
    $this->assertEqual($new_user->uid, $entity->user->id, 'Updated user id can be read.');
    $this->assertEqual($new_user->name, $entity->user->entity->name, 'Updated user name value can be read.');

    // Change the assigned user by id.
    $new_user = $this->drupalCreateUser();
    $entity->user->id = $new_user->uid;
    $this->assertEqual($new_user->uid, $entity->user->id, 'Updated user id can be read.');
    $this->assertEqual($new_user->name, $entity->user->entity->name, 'Updated user name value can be read.');
  }

  /**
   * Tries to save and load an entity again.
   */
  function testSave() {
    $entity = $this->createTestEntity();
    $entity->save();
    $this->assertTrue((bool) $entity->id(), 'Entity has received an id.');

    $entity = entity_load('entity_test', $entity->id());
    $this->assertTrue((bool) $entity->id(), 'Entity loaded.');

    // Access the name property.
    $this->assertEqual($this->entity_name, $entity->name->value, 'Name value can be read.');
    $this->assertEqual($this->entity_user->uid, $entity->user->id, 'User id can be read.');
    $this->assertEqual($this->entity_user->name, $entity->user->entity->name, 'User name can be read.');
  }

  /**
   * Tests introspection and getting metadata upfront.
   */
  function testIntrospection() {
    // Test getting metadata upfront, i.e. without having an entity object.
    $definition = array(
      'type' => 'entity',
      'entity type' => 'entity_test',
      'label' => t('Test entity'),
    );
    $property_entity = drupal_get_property($definition);
    $property_definitions = $property_entity->getPropertyDefinitions($definition);
    $this->assertEqual($property_definitions['name']['type'], 'string_item', 'Name property found.');
    $this->assertEqual($property_definitions['user']['type'], 'entityreference_item', 'User property found.');

    // Test introspecting an entity object.
    // @todo: Add bundles and test bundles as well.
    $entity = entity_create('entity_test', array());

    $definitions = $entity->getPropertyDefinitions();
    $this->assertEqual($definitions['name']['type'], 'string_item', 'Name property found.');
    $this->assertEqual($definitions['user']['type'], 'entityreference_item', 'User property found.');

    $name_properties = $entity->name->getPropertyDefinitions();
    $this->assertEqual($name_properties['value']['type'], 'string', 'String value property of the name found.');

    $userref_properties = $entity->user->getPropertyDefinitions();

    $this->assertEqual($userref_properties['id']['type'], 'integer', 'Entity id property of the user found.');
    $this->assertEqual($userref_properties['entity']['type'], 'entity', 'Entity reference property of the user found.');

    // @todo: Once the user entity has definitions, continue testing getting
    // them from the $userref_values['entity'] property.
  }

  /**
   * Tests iterating over properties.
   */
  function testIterator() {
    $entity = $this->createTestEntity();

    foreach ($entity as $name => $property) {
      $this->assertTrue($property instanceof EntityPropertyInterface, "Property $name implements interface.");

      foreach ($property as $delta => $item) {
        $this->assertTrue($property[0] instanceof EntityPropertyItemInterface, "Item $delta of property $name implements interface.");

        foreach ($item as $value_name => $value_property) {
          $value = $value_property->getValue();
          $this->assertTrue(is_scalar($value) || $value instanceof \Drupal\entity\EntityInterface, "Value $value_name of item $delta of property $name is a primitive or an entity.");
        }
      }
    }

    $properties = $entity->getProperties();
    $this->assertEqual(array_keys($properties), array_keys($entity->getPropertyDefinitions()), 'All properties returned.');
    $this->assertEqual($properties, iterator_to_array($entity->getIterator()), 'Entity iterator iterates over all properties.');
  }

  /**
   * Tests working with entity properties based upon property container and property list interfaces.
   */
  function testPropertyContainerInterfaces() {
    $entity = $this->createTestEntity();
    $entity_definition = array(
      'type' => 'entity',
      'entity type' => 'entity_test',
      'label' => t('Test entity'),
    );

    // For the test we navigate through the tree of contained properties and get
    // all contained strings, limited by a certain depth.
    $strings = array();
    $this->getContainedStrings($entity, $entity_definition, 0, $strings);

    // @todo: Once the user entity has defined properties this should contain
    // the user name and other user entity strings as well.
    $this->assertEqual($strings, array($this->entity_name), 'All contained strings found.');
  }

  /**
   * Recursive helper for getting all contained strings.
   */
  function getContainedStrings($data_item, array $definition, $depth, array &$strings) {

    if ($definition['type'] == 'string') {
      $strings[] = $data_item->getValue();
    }

    // Recurse until a certain depth is reached if possible.
    if ($depth < 7) {
      if ($data_item instanceof \Drupal\Core\Property\PropertyListInterface) {
        foreach ($data_item as $item) {
          $this->getContainedStrings($item, $definition, ++$depth, $strings);
        }
      }
      elseif ($data_item instanceof \Drupal\Core\Property\PropertyContainerInterface) {
        foreach ($data_item as $name => $property) {
          $property_definition = $data_item->getPropertyDefinition($name);
          $this->getContainedStrings($property, $property_definition, ++$depth, $strings);
        }
      }
    }
  }
}
