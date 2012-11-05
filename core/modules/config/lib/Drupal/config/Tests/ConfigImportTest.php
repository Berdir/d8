<?php

/**
 * @file
 * Definition of Drupal\config\Tests\ConfigImportTest.
 */

namespace Drupal\config\Tests;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests importing configuration from files into active configuration.
 */
class ConfigImportTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('config_test');

  public static function getInfo() {
    return array(
      'name' => 'Import configuration',
      'description' => 'Tests importing configuration from files into active configuration.',
      'group' => 'Configuration',
    );
  }

  function setUp() {
    parent::setUp();

    config_install_default_config('module', 'config_test');
    // Installing config_test's default configuration pollutes the global
    // variable being used for recording hook invocations by this test already,
    // so it has to be cleared out manually.
    unset($GLOBALS['hook_config_test']);
  }

  /**
   * Tests omission of module APIs for bare configuration operations.
   */
  function testNoImport() {
    $dynamic_name = 'config_test.dynamic.default';

    // Verify the default configuration values exist.
    $config = config($dynamic_name);
    $this->assertIdentical($config->get('id'), 'default');

    // Verify that a bare config() does not involve module APIs.
    $this->assertFalse(isset($GLOBALS['hook_config_test']));
  }

  /**
   * Tests deletion of configuration during import.
   */
  function testDeleted() {
    $dynamic_name = 'config_test.dynamic.default';
    $storage = $this->container->get('config.storage');
    $staging = $this->container->get('config.storage.staging');

    // Verify the default configuration values exist.
    $config = config($dynamic_name);
    $this->assertIdentical($config->get('id'), 'default');

    // Create an empty manifest to delete the configuration object.
    $staging->write('manifest.config_test.dynamic', array());
    // Import.
    config_import();

    // Verify the values have disappeared.
    $this->assertIdentical($storage->read($dynamic_name), FALSE);

    $config = config($dynamic_name);
    $this->assertIdentical($config->get('id'), NULL);

    // Verify that appropriate module API hooks have been invoked.
    $this->assertTrue(isset($GLOBALS['hook_config_test']['load']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['presave']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['insert']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['update']));
    $this->assertTrue(isset($GLOBALS['hook_config_test']['predelete']));
    $this->assertTrue(isset($GLOBALS['hook_config_test']['delete']));

    // Verify that there is nothing more to import.
    $this->assertFalse(config_sync_get_changes($staging, $storage));
  }

  /**
   * Tests creation of configuration during import.
   */
  function testNew() {
    $dynamic_name = 'config_test.dynamic.new';
    $storage = $this->container->get('config.storage');
    $staging = $this->container->get('config.storage.staging');

    // Verify the configuration to create does not exist yet.
    $this->assertIdentical($storage->exists($dynamic_name), FALSE, $dynamic_name . ' not found.');

    $this->assertIdentical($staging->exists($dynamic_name), FALSE, $dynamic_name . ' not found.');

    // Create new config entity.
    $original_dynamic_data = array(
      'id' => 'new',
      'uuid' => '30df59bd-7b03-4cf7-bb35-d42fc49f0651',
      'label' => 'New',
      'style' => '',
      'langcode' => 'und',
    );
    $staging->write($dynamic_name, $original_dynamic_data);

    // Create manifest for new config entity.
    $manifest_data = config('manifest.config_test.dynamic')->get();
    $manifest_data[$original_dynamic_data['id']]['name'] = 'config_test.dynamic.' . $original_dynamic_data['id'];
    $staging->write('manifest.config_test.dynamic', $manifest_data);

    $this->assertIdentical($staging->exists($dynamic_name), TRUE, $dynamic_name . ' found.');

    // Import.
    config_import();

    // Verify the values appeared.
    $config = config($dynamic_name);
    $this->assertIdentical($config->get('label'), $original_dynamic_data['label']);

    // Verify that appropriate module API hooks have been invoked.
    $this->assertFalse(isset($GLOBALS['hook_config_test']['load']));
    $this->assertTrue(isset($GLOBALS['hook_config_test']['presave']));
    $this->assertTrue(isset($GLOBALS['hook_config_test']['insert']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['update']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['predelete']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['delete']));

    // Verify that there is nothing more to import.
    $this->assertFalse(config_sync_get_changes($staging, $storage));
  }

  /**
   * Tests updating of configuration during import.
   */
  function testUpdated() {
    $name = 'config_test.system';
    $dynamic_name = 'config_test.dynamic.default';
    $storage = $this->container->get('config.storage');
    $staging = $this->container->get('config.storage.staging');

    // Verify that the configuration objects to import exist.
    $this->assertIdentical($storage->exists($name), TRUE, $name . ' found.');
    $this->assertIdentical($storage->exists($dynamic_name), TRUE, $dynamic_name . ' found.');

    // Replace the file content of the existing configuration objects in the
    // staging directory.
    $original_name_data = array(
      'foo' => 'beer',
    );
    $staging->write($name, $original_name_data);
    $original_dynamic_data = $storage->read($dynamic_name);
    $original_dynamic_data['label'] = 'Updated';
    $staging->write($dynamic_name, $original_dynamic_data);
    // Create manifest for updated config entity.
    $manifest_data = config('manifest.config_test.dynamic')->get();
    $staging->write('manifest.config_test.dynamic', $manifest_data);

    // Verify the active configuration still returns the default values.
    $config = config($name);
    $this->assertIdentical($config->get('foo'), 'bar');
    $config = config($dynamic_name);
    $this->assertIdentical($config->get('label'), 'Default');

    // Import.
    config_import();

    // Verify the values were updated.
    $config = config($name);
    $this->assertIdentical($config->get('foo'), 'beer');
    $config = config($dynamic_name);
    $this->assertIdentical($config->get('label'), 'Updated');

    // Verify that the original file content is still the same.
    $this->assertIdentical($staging->read($name), $original_name_data);
    $this->assertIdentical($staging->read($dynamic_name), $original_dynamic_data);

    // Verify that appropriate module API hooks have been invoked.
    $this->assertTrue(isset($GLOBALS['hook_config_test']['load']));
    $this->assertTrue(isset($GLOBALS['hook_config_test']['presave']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['insert']));
    $this->assertTrue(isset($GLOBALS['hook_config_test']['update']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['predelete']));
    $this->assertFalse(isset($GLOBALS['hook_config_test']['delete']));

    // Verify that there is nothing more to import.
    $this->assertFalse(config_sync_get_changes($staging, $storage));
  }

}
