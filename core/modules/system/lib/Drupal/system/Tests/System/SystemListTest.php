<?php

/**
 * @file
 * Contains \Drupal\system\Tests\System\SystemListTest.
 */

namespace Drupal\system\Tests\System;

use Drupal\Core\Cache\MemoryCounterBackend;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Defines a test for the system_list() function.
 */
class SystemListTest extends DrupalUnitTestBase {

  /**
   * The memory backend to use for the test.
   *
   * @var MemoryCounterBackend
   */
  protected $memoryCounterBackend;

  public static function getInfo() {
    return array(
      'name' => 'System List',
      'description' => 'Tests the system_list() function in system.module.',
      'group' => 'System',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function containerBuild(ContainerBuilder $container) {
    parent::containerBuild($container);

    $container->register('cache.backend.memory_counter', 'Drupal\Core\Cache\MemoryCounterBackendFactory');
    $this->settingsSet('cache', array('bootstrap' => 'cache.backend.memory_counter'));
  }

  /**
   * Test system_list().
   */
  public function testSystemList() {
    // Ensure that the static cache of system_list() is empty.
    $lists = &drupal_static('system_list');
    $lists = array();

    $this->memoryCounterBackend = $this->container->get('cache.bootstrap');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'system_list'), 0, 'system_list cache has not yet been called.');

    // Get system list which will load the information from the cache.
    system_list('theme');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'system_list'), 1, 'system_list cache has been called once.');

    // Get system list again, which will load the information from the static
    // cache.
    system_list('theme');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'system_list'), 1, 'system_list cache has been called once.');
  }

}
