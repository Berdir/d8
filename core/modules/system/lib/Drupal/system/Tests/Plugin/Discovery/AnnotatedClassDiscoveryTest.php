<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Plugin\Discovery\AnnotatedClassDiscoveryTest.
 */

namespace Drupal\system\Tests\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;

/**
 * Tests that plugins with annotated classes are correctly discovered.
 */
class AnnotatedClassDiscoveryTest extends DiscoveryTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Annotated class discovery',
      'description' => 'Tests that plugins are correctly discovered using annotated classes.',
      'group' => 'Plugin API',
    );
  }

  public function setUp() {
    parent::setUp();

    // Make sure that the plugin_test.module namespace is registered.
    drupal_classloader_register('plugin_test', dirname(drupal_get_filename('module', 'plugin_test')));

    $this->expectedDefinitions = array(
      'apple' => array(
        'id' => 'apple',
        'label' => 'Apple',
        'color' => 'green',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\fruit\Apple',
      ),
      'cherry' => array(
        'id' => 'cherry',
        'label' => 'Cherry',
        'color' => 'red',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\fruit\Cherry',
      ),
      'orange' => array(
        'id' => 'orange',
        'label' => 'Orange',
        'color' => 'orange',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\fruit\Orange',
      ),
    );
    $this->discovery = new AnnotatedClassDiscovery('plugin_test', 'fruit');
    $this->emptyDiscovery = new AnnotatedClassDiscovery('non_existing_module', 'non_existing_plugin_type');
  }
}

