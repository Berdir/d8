<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\DefaultPluginManagerTest.
 */

namespace Drupal\Tests\Core\Plugin;

use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Tests\UnitTestCase;

// @todo Remove once http://drupal.org/node/1620010 is committed.
if (!defined('LANGUAGE_TYPE_INTERFACE')) {
  define('LANGUAGE_TYPE_INTERFACE', 'language_interface');
}
if (!defined('LANGUAGE_LTR')) {
  define('LANGUAGE_LTR', 0);
}
if (!defined('LANGUAGE_RTL')) {
  define('LANGUAGE_RTL', 1);
}

/**
 * Tests the DefaultPluginManager.
 *
 * @group Plugin
 */
class DefaultPluginManagerTest extends UnitTestCase {

  /**
   * The expected plugin definitions.
   *
   * @var array
   */
  protected $expectedDefinitions;

  /**
   * The namespaces to look for plugin definitions.
   *
   * @var \Traversable
   */
  protected $namespaces;

  public static function getInfo() {
    return array(
      'name' => 'Default Plugin Manager',
      'description' => 'Tests the DefaultPluginManager class.',
      'group' => 'Plugin',
    );
  }

  function setUp() {
    $this->expectedDefinitions = array(
      'apple' => array(
        'id' => 'apple',
        'label' => 'Apple',
        'color' => 'green',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\fruit\Apple',
      ),
      'banana' => array(
        'id' => 'banana',
        'label' => 'Banana',
        'color' => 'yellow',
        'uses' => array(
          'bread' => 'Banana bread',
        ),
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\fruit\Banana',
      ),
    );

    $this->namespaces = new \ArrayObject(array('Drupal\plugin_test' => DRUPAL_ROOT . '/core/modules/system/tests/modules/plugin_test/lib'));
  }

  /**
   * Tests the plugin manager with no cache and altering.
   */
  function testDefaultPluginManager() {
    $plugin_manager = new TestPluginManager($this->namespaces, $this->expectedDefinitions);
    $this->assertEquals($this->expectedDefinitions, $plugin_manager->getDefinitions());
  }

  /**
   * Tests the plugin manager with no cache and altering.
   */
  function testDefaultPluginManagerWithAlter() {
    $module_handler = $this->getMock('Drupal\Core\Extension\ModuleHandler');

    // Configure the stub.
    $alter_hook_name = $this->randomName();
    $module_handler->expects($this->once())
      ->method('alter')
      ->with($this->equalTo($alter_hook_name), $this->equalTo($this->expectedDefinitions));

    $plugin_manager = new TestPluginManager($this->namespaces, $this->expectedDefinitions);
    $plugin_manager->setAlterHook($module_handler, $alter_hook_name);

    $this->assertEquals($this->expectedDefinitions, $plugin_manager->getDefinitions());
  }

  /**
   * Tests the plugin manager with caching and altering.
   */
  function testDefaultPluginManagerWithEmptyCache() {
    $cid = $this->randomName();
    $cache_backend = $this->getMockBuilder('Drupal\Core\Cache\MemoryBackend')
      ->disableOriginalConstructor()
      ->getMock();
    $cache_backend
      ->expects($this->once())
      ->method('get')
      ->with($cid . ':en')
      ->will($this->returnValue(FALSE));
    $cache_backend
      ->expects($this->once())
      ->method('set')
      ->with($cid . ':en', $this->expectedDefinitions);

    $language = new Language(array('langcode' => 'en'));
    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManager');
    $language_manager->expects($this->once())
      ->method('getLanguage')
      ->with(LANGUAGE_TYPE_INTERFACE)
      ->will($this->returnValue($language));

    $plugin_manager = new TestPluginManager($this->namespaces, $this->expectedDefinitions);
    $plugin_manager->setCache($cache_backend, $language_manager, $cid);

    $this->assertEquals($this->expectedDefinitions, $plugin_manager->getDefinitions());
  }

  /**
   * Tests the plugin manager with caching and altering.
   */
  function testDefaultPluginManagerWithFilledCache() {
    $cid = $this->randomName();
    $cache_backend = $this->getMockBuilder('Drupal\Core\Cache\MemoryBackend')
      ->disableOriginalConstructor()
      ->getMock();
    $cache_backend
      ->expects($this->once())
      ->method('get')
      ->with($cid . ':en')
      ->will($this->returnValue((object) array('data' => $this->expectedDefinitions)));
    $cache_backend
      ->expects($this->never())
      ->method('set');

    $language = new Language(array('langcode' => 'en'));
    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManager');
    $language_manager->expects($this->once())
      ->method('getLanguage')
      ->with(LANGUAGE_TYPE_INTERFACE)
      ->will($this->returnValue($language));

    $plugin_manager = new TestPluginManager($this->namespaces, $this->expectedDefinitions);
    $plugin_manager->setCache($cache_backend, $language_manager, $cid);

    $this->assertEquals($this->expectedDefinitions, $plugin_manager->getDefinitions());
  }

}
