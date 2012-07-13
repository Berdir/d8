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
   * Tests reading and writing properties and property items.
   */
  public function testReadWrite() {
    $name = $this->randomName();
    $name_property[0]['value'] = $name;
    $user = $this->drupalCreateUser();

    // Pass in the value of the name property when creating. With the user
    // property we test setting a property after creation.
    $entity = entity_create('entity_test', array('name' => $name_property));
    $entity->user->id = $user->uid;

    // Access the name property.
    $this->assertTrue($entity->name instanceof EntityPropertyInterface, 'Property implements interface');
    $this->assertTrue($entity->name[0] instanceof EntityPropertyItemInterface, 'Property item implements interface');

    $this->assertEqual($name, $entity->name->value, 'Name value can be read.');
    $this->assertEqual($name, $entity->name[0]->value, 'Name value can be read through list access.');
    $this->assertEqual($entity->getRawValue('name'), array(0 => array('value' => $name)), 'Raw property value returned.');

    // Change the name.
    $new_name = $this->randomName();
    $entity->name->value = $new_name;
    $this->assertEqual($new_name, $entity->name->value, 'Name can be updated and read.');
    $this->assertEqual($entity->getRawValue('name'), array(0 => array('value' => $new_name)), 'Raw property value reflects the update.');

    $new_name = $this->randomName();
    $entity->name[0]->value = $new_name;
    $this->assertEqual($new_name, $entity->name->value, 'Name can be updated and read through list access.');

    // Access the user property.
    $this->assertTrue($entity->user instanceof EntityPropertyInterface, 'Property implements interface');
    $this->assertTrue($entity->user[0] instanceof EntityPropertyItemInterface, 'Property item implements interface');

    $this->assertEqual($user->uid, $entity->user->id, 'User id can be read.');
    $this->assertEqual($user->name, $entity->user->entity->name, 'User name can be read.');

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
    $name = $this->randomName();
    $name_property[0]['value'] = $name;
    $user = $this->drupalCreateUser();

    // Pass in the value of the name property when creating. With the user
    // property we test setting a property after creation.
    $entity = entity_create('entity_test', array('name' => $name_property));
    $entity->user->id = $user->uid;

    $entity->save();
    $this->assertTrue((bool) $entity->id(), 'Entity has received an id.');

    $entity = entity_load('entity_test', $entity->id());
    $this->assertTrue((bool) $entity->id(), 'Entity loaded.');

    // Access the name property.
    $this->assertEqual($name, $entity->name->value, 'Name value can be read.');
    $this->assertEqual($user->uid, $entity->user->id, 'User id can be read.');
    $this->assertEqual($user->name, $entity->user->entity->name, 'User name can be read.');
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
    $data_type = drupal_get_property_type_plugin($definition['type']);
    $property_definitions = $data_type->getPropertyDefinitions($definition);
    $this->assertEqual($property_definitions['name']['type'], 'text_item', 'Name property found.');
    $this->assertEqual($property_definitions['user']['type'], 'entityreference_item', 'User property found.');

    // Test introspecting an entity object.
    // @todo: Add bundles and test bundles as well.
    $entity = entity_create('entity_test', array());

    $definitions = $entity->getPropertyDefinitions();
    $this->assertEqual($definitions['name']['type'], 'text_item', 'Name property found.');
    $this->assertEqual($definitions['user']['type'], 'entityreference_item', 'User property found.');

    $definition = $entity->getPropertyDefinition('name');
    $data_type = drupal_get_property_type_plugin($definition['type']);
    $name_properties = $data_type->getPropertyDefinitions($definition);
    $this->assertEqual($name_properties['value']['type'], 'string', 'String value property of the name found.');

    $definition = $entity->getPropertyDefinition('user');
    $data_type = drupal_get_property_type_plugin($definition['type']);
    $userref_values = $data_type->getPropertyDefinitions($definition);

    $this->assertEqual($userref_values['id']['type'], 'integer', 'Entity id property of the user found.');
    $this->assertEqual($userref_values['entity']['type'], 'entity', 'Entity reference property of the user found.');

    // @todo: Once the user entity has definitions, continue testing getting
    // them from the $userref_values['entity'] definition.
  }

  /**
   * Tests iterating over properties.
   */
  function testIterator() {
    $name = $this->randomName();
    $name_property[0]['value'] = $name;
    $user = $this->drupalCreateUser();

    // Pass in the value of the name property when creating. With the user
    // property we test setting a property after creation.
    $entity = entity_create('entity_test', array('name' => $name_property));
    $entity->user->id = $user->uid;

    foreach ($entity as $name => $property) {
      $this->assertTrue($property instanceof EntityPropertyInterface, "Property $name implements interface.");

      foreach ($property as $delta => $item) {
        $this->assertTrue($property[0] instanceof EntityPropertyItemInterface, "Item $delta of property $name implements interface.");

        foreach ($item as $value_name => $value) {
          $this->assertTrue(is_scalar($value) || $value instanceof \Drupal\entity\EntityInterface, "Value $value_name of item $delta of property $name is a primitive or an entity.");
        }
      }
    }

    $properties = $entity->getProperties();
    $this->assertEqual(array_keys($properties), array_keys($entity->getPropertyDefinitions()), 'All properties returned.');
    $this->assertEqual($properties, iterator_to_array($entity->getIterator()), 'Entity iterator iterates over all properties.');
  }
}
