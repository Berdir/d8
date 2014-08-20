<?php

/**
 * @file
 * Contains Drupal\system\Tests\KeyValueStore\MemoryStorageTest.
 */

namespace Drupal\system\Tests\KeyValueStore;

use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Tests the key-value memory storage.
 *
 * @group KeyValueStore
 */
class MemoryStorageTest extends StorageTestBase {

  protected function setUp() {
    parent::setUp();
    $this->settingsSet('keyvalue_default', 'keyvalue.memory');
  }

  /**
   * @param ContainerBuilder $container
   */
  public function containerBuild(ContainerBuilder $container) {
    $container->register('keyvalue.memory', 'Drupal\Core\KeyValueStore\KeyValueMemoryFactory');
  }

}
