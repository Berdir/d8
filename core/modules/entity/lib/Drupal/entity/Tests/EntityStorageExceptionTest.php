<?php

/**
 * @file
 * Definition of Drupal\entity\Tests\EntityStorageExceptionTest.
 */

namespace Drupal\entity\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\entity\EntityStorageException;

/**
 * Tests the basic Entity API Storage Exceptions.
 */
class EntityStorageExceptionTest extends WebTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Entity Storage Exception Test',
      'description' => 'Tests for entity storage exceptions.',
      'group' => 'Entity API',
    );
  }

  function setUp() {
    parent::setUp('entity', 'entity_test', 'entity_storage_exception_test');
  }

  /**
   * Test to check if exceptions are thrown properly in entity API while saving
   * or deleting an entity.
   */
  public function testEntityStorageExceptions() {
    $user1 = $this->drupalCreateUser();

    variable_del('entity_storage_exception_test_throw_exception');
    $entity = entity_create('entity_test', array('name' => 'test', 'uid' => $user1->uid));
    try {
      variable_set('entity_storage_exception_test_throw_exception', TRUE);
      $entity->save();
      $this->fail('TEST1: Entity presave EntityStorageException thrown but not caught.');
    }
    catch (EntityStorageException $e) {
      $this->assertEqual($e->getcode(), 1, 'TEST1: Entity presave EntityStorageException caught.');
    }

    variable_del('entity_storage_exception_test_throw_exception');
    $entity = entity_create('entity_test', array('name' => 'test2', 'uid' => $user1->uid));
    try {
      variable_set('entity_storage_exception_test_throw_exception', FALSE);
      $entity->save();
      $this->pass('TEST2: Exception presave not thrown and not caught');
    }
    catch (EntityStorageException $e) {
      $this->assertNotEqual($e->getCode(), 1, 'TEST2: Entity presave EntityStorageException caught');
    }

    variable_del('entity_storage_exception_test_throw_exception');
    $entity = entity_create('entity_test', array('name' => 'test3', 'uid' => $user1->uid));
    $entity->save();
    try {
      variable_set('entity_storage_exception_test_throw_exception', TRUE);
      $entity->delete();
      $this->fail('TEST3: Entity predelete EntityStorageException not thrown');
    }
    catch(EntityStorageException $e) {
      $this->assertEqual($e->getCode(), 2, 'TEST3: Entity predelete EntityStorageException caught');
    }

    variable_del('entity_storage_exception_test_throw_exception');
    $entity = entity_create('entity_test', array('name' => 'test4', 'uid' => $user1->uid));
    $entity->save();
    try {
      variable_set('entity_storage_exception_test_throw_exception', FALSE);
      $entity->delete();
      $this->pass('TEST4: Entity predelete EntityStorageException not thrown and not caught');

    }
    catch(EntityStorageException $e) {
      $this->assertNotEqual($e->getCode(), 2, 'TEST4: Entity predelete EntityStorageException thrown');
    }
  }
}