<?php

/**
 * @file
 * Contains Drupal\system\Tests\KeyValueStore\DatabaseStorageTest.
 */

namespace Drupal\system\Tests\KeyValueStore;

/**
 * Tests the key-value database storage.
 *
 * @group KeyValueStore
 */
class DatabaseStorageTest extends StorageTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system');

  protected function setUp() {
    parent::setUp();
    $this->installSchema('system', array('key_value'));
    $this->settingsSet('keyvalue_default', 'keyvalue.database');
  }

}
