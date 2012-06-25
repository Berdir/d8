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
    $user = $this->drupalCreateUser();
    $entity = entity_create('entity_test', array('name' => $name, 'uid' => $user->uid));

    // Access the name property.
    $this->assertTrue($entity->name instanceof EntityPropertyInterface, 'Property implements interface');
    $this->assertTrue($entity->name[0] instanceof EntityPropertyItemInterface, 'Property item implements interface');

    $this->assertEqual($name, $entity->name->value, 'Name value can be read.');
    $this->assertEqual($name, $entity->name[0]->value, 'Name value can be read through list access.');

    // Change the name.
    $new_name = $this->randomName();
    $entity->name->value = $new_name;
    $this->assertEqual($new_name, $entity->name->value, 'Updated name value can be read.');

    return;
    // Access the user property.
    $this->assertTrue($entity->user instanceof EntityPropertyInterface, 'Property implements interface');
    $this->assertTrue($entity->user[0] instanceof EntityPropertyItemInterface, 'Property item implements interface');

    $this->assertEqual($user->uid, $entity->user->value, 'User id can be read.');
    $this->assertEqual($user->name, $entity->user->entity->name, 'User name value can be read.');

    // Change the assigned user.
    $new_user = $this->drupalCreateUser();
    $entity->user->entity = $new_user;
    $this->assertEqual($new_user->uid, $entity->user->value, 'User id can be read.');
    $this->assertEqual($new_user->name, $entity->user->entity->name, 'User name value can be read.');
  }

  /**
   * Tries to save and load an entity again.
   */
  function testSave() {
    return;
    $name = $this->randomName();
    $user = $this->drupalCreateUser();
    $entity = entity_create('entity_test', array('name' => $name, 'uid' => $user->uid));
    $entity->save();

    debug($entity->id());
    debug($entity);
    $entity = entity_load('entity_test', $entity->id());
    debug($entity);

    // Access the name property.
    $this->assertTrue($entity->name instanceof EntityPropertyInterface, 'Property implements interface');
    $this->assertTrue($entity->name[0] instanceof EntityPropertyItemInterface, 'Property item implements interface');

    $this->assertEqual($name, $entity->name->value, 'Name value can be read.');
    $this->assertEqual($name, $entity->name[0]->value, 'Name value can be read through list access.');

  }
}
