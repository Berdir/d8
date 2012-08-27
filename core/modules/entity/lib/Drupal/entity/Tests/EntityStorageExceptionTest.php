<?php

/**
 * @file
 * Definition of Drupal\entity\Tests\EntityStorageExceptionTest.
 */

namespace Drupal\entity\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\entity\EntityStorageException;

/**
 * Tests the basic Entity storage exceptions.
 */
class EntityStorageExceptionTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_test', 'entity_storage_exception_test');

  public static function getInfo() {
    return array(
      'name' => 'Entity storage exception',
      'description' => 'Tests for entity storage exceptions.',
      'group' => 'Entity API',
    );
  }

  /**
   * Check if exceptions are thrown properly while saving or deleting an entity.
   */
  public function testEntityStorageExceptions() {
    $user1 = $this->drupalCreateUser();

    $entity = entity_create('entity_test', array('name' => 'test', 'uid' => $user1->uid));
    try {
      variable_set('entity_storage_exception_test_throw_exception', TRUE);
      $entity->save();
      $this->fail('Entity presave EntityStorageException thrown but not caught.');
    } catch (EntityStorageException $e) {
      $this->assertEqual($e->getcode(), 1, 'Entity presave EntityStorageException caught.');
    }

    $entity = entity_create('entity_test', array('name' => 'test2', 'uid' => $user1->uid));
    try {
      variable_set('entity_storage_exception_test_throw_exception', FALSE);
      $entity->save();
      $this->pass('Exception presave not thrown and not caught');
    } catch (EntityStorageException $e) {
      $this->assertNotEqual($e->getCode(), 1, 'Entity presave EntityStorageException caught');
    }

    $entity = entity_create('entity_test', array('name' => 'test3', 'uid' => $user1->uid));
    $entity->save();
    try {
      variable_set('entity_storage_exception_test_throw_exception', TRUE);
      $entity->delete();
      $this->fail('Entity predelete EntityStorageException not thrown');
    } catch (EntityStorageException $e) {
      $this->assertEqual($e->getCode(), 2, 'Entity predelete EntityStorageException caught');
    }

    variable_set('entity_storage_exception_test_throw_exception', FALSE);
    $entity = entity_create('entity_test', array('name' => 'test4', 'uid' => $user1->uid));
    $entity->save();
    try {
      variable_set('entity_storage_exception_test_throw_exception', FALSE);
      $entity->delete();
      $this->pass('Entity predelete EntityStorageException not thrown and not caught');
    } catch (EntityStorageException $e) {
      $this->assertNotEqual($e->getCode(), 2, 'Entity predelete EntityStorageException thrown');
    }
  }

}