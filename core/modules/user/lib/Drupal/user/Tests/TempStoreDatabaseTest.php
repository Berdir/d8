<?php

/**
 * @file
 * Definition of Drupal\user\Tests\TempStoreDatabaseTest.
 */

namespace Drupal\user\Tests;

use Drupal\simpletest\UnitTestBase;
use Drupal\user\TempStoreFactory;
use Drupal\Core\Lock\DatabaseLockBackend;
use Drupal\Core\Database\Database;

/**
 * Tests the TempStore namespace.
 *
 * @see Drupal\Core\TempStore\TempStore.
 */
class TempStoreDatabaseTest extends UnitTestBase {

  /**
   * A key/value store factory.
   *
   * @var Drupal\user\TempStoreFactory
   */
  protected $storeFactory;

  /**
   * The name of the key/value collection to set and retrieve.
   *
   * @var string
   */
  protected $collection;

  /**
   * An array of (fake) user IDs.
   *
   * @var array
   */
  protected $users = array();

  /**
   * An array of random stdClass objects.
   *
   * @var array
   */
  protected $objects = array();

  public static function getInfo() {
    return array(
      'name' => 'TempStore',
      'description' => 'Tests the temporary object storage system.',
      'group' => 'TempStore',
    );
  }

  protected function setUp() {
    parent::setUp();

    // Install system tables to test the key/value storage without installing a
    // full Drupal environment.
    module_load_install('system');
    $schema = system_schema();
    db_create_table('semaphore', $schema['semaphore']);
    db_create_table('key_value_expire', $schema['key_value_expire']);

    // Create a key/value collection.
    $this->storeFactory = new TempStoreFactory(Database::getConnection(), new DatabaseLockBackend());
    $this->collection = $this->randomName();

    // Create four users and objects for testing.
    for ($i = 0; $i <= 3; $i++) {
      $this->objects[$i] = $this->randomObject();
      $this->users[$i] = mt_rand(500, 5000000);
    }
  }

  /**
   * Generates a random PHP object.
   *
   * @param int $size
   *   The number of random keys to add to the object.
   *
   * @return \stdClass
   *   The generated object, with the specified number of random keys. Each key
   *   has a random string value.
   */
  public function randomObject($size = 4) {
    $object = new \stdClass();
    for ($i = 0; $i < $size; $i++) {
      $random_key = $this->randomName();
      $random_value = $this->randomString();
      $object->{$random_key} = $random_value;
    }
    return $object;
  }

  /**
   * Tests the UserTempStore API.
   */
  public function testUserTempStore() {
    $key = $this->randomName();
    // Test that setIfNotExists() succeeds only the first time.
    for ($i = 0; $i <= 1; $i++) {
      $store = $this->getStorePerUID($this->users[$i]);
      // setIfNotExists() should fail the second time ($i = 1).
      $this->assertEqual(!$i, $store->setIfNotExists($key, $this->objects[$i]));
      $metadata = $store->getMetadata($key);
      $this->assertEqual($this->users[0], $metadata->owner);
      $this->assertIdenticalObject($this->objects[0], $store->get($key));
    }

    // Remove the item and try to set it again.
    $store->delete($key);
    $store->setIfNotExists($key, $this->objects[1]);
    // This time it should succeed.
    $this->assertIdenticalObject($this->objects[1], $store->get($key));

    // This user can update the object.
    $store->set($key, $this->objects[2]);
    $this->assertIdenticalObject($this->objects[2], $store->get($key));
    // But another can't.
    $store = $this->getStorePerUID($this->users[2]);
    $store->set($key, $this->objects[3]);
    $this->assertIdenticalObject($this->objects[2], $store->get($key));

    // Now manually expire the item (this is not exposed by the API) and then
    // assert it is no longer accessible.
    db_update('key_value_expire')
      ->fields(array('expire' => REQUEST_TIME - 1))
      ->condition('collection', $this->collection)
      ->condition('name', $key)
      ->execute();
    $this->assertFalse($store->get($key));
  }

  /**
   * Returns a TempStore belonging to the passed in user.
   *
   * @param int $uid
   *   A user ID.
   *
   * @return Drupal\user\TempStore
   *   The key/value store object.
   */
  protected function getStorePerUID($uid) {
    // TempStoreFactory::get() currently relies on the logged-in user ID, so
    // set it in globals to test the method.
    $GLOBALS['user']->uid = $uid;
    return $this->storeFactory->get($this->collection);
  }

  /**
   * Checks to see if two objects are identical.
   *
   * @param object $object1
   *   The first object to check.
   * @param object $object2
   *   The second object to check.
   */
  protected function assertIdenticalObject($object1, $object2) {
    $identical = TRUE;
    foreach ($object1 as $key => $value) {
      $identical = $identical && isset($object2->$key) && $object2->$key === $value;
    }
    $this->assertTrue($identical, format_string('!object1 is identical to !object2', array(
      '!object1' => var_export($object1, TRUE),
      '!object2' => var_export($object2, TRUE),
    )));
  }
}
