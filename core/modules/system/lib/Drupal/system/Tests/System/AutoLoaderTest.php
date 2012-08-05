<?php

/**
 * @file
 * Definition of Drupal\system\Tests\System\AutoLoaderTest.
 */

namespace Drupal\system\Tests\System;

use Drupal\simpletest\WebTestBase;

/**
 * Tests that the autoloader can correctly find all available classes.
 */
class AutoLoaderTest extends WebTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Autoloader functionality',
      'description' => 'Tests that classes are stored in the filesystem in a way that allows the autoloader to find them.',
      'group' => 'System',
    );
  }

  public function setUp() {
    parent::setUp('autoloader_test');
  }

  /**
   * Tests module-provided classes.
   *
   * The Drupal core auto-loader requires that classes follow the PSR-0
   * convention in order to be autoloaded. This means that if a typo is ever
   * made in the class name, namespace, or location in the filesystem, it will
   * not be loaded. (This is particularly bad for test classes, since their
   * failure to be found and run will generally happen silently and therefore
   * not be noticed.)
   *
   * To protect against that, this test ensures that all classes within each
   * core module's "lib" directory are able to be found by the autoloader.
   */
  public function testModuleClasses() {
    // Get a list of the currently loaded classes.
    $current_classes = get_declared_classes();

    // We will sea rch all core modules for new classes.
    $modules = system_rebuild_module_data();
    foreach ($modules as $name => $module) {
      if ($module->info['package'] != 'Core') {
        unset($modules[$name]);
      }
    }
    $this->assertTrue(count($modules), format_string('Found @count modules to search for classes: %modules', array(
      '@count' => count($modules),
      '%modules' => implode(', ', array_keys($modules)),
    )));

    // The modules must be enabled in order for the autoloader to work with
    // them.
    module_enable(array_keys($modules));

    // Search the "lib" directory of each module for files which might contain
    // class definitions.
    $files = array();
    foreach ($modules as $module) {
      $lib_dir = DRUPAL_ROOT . '/' . dirname($module->filename) . '/lib';
      if (is_dir($lib_dir)) {
        $files = array_merge($files, file_scan_directory($lib_dir, '/.*\.php$/'));
      }
    }
    $this->assertTrue(count($files), format_string('Found @count files to search for classes.', array(
      '@count' => count($files),
    )));

    // Load each file so that each class is now in memory, and determine which
    // classes are new.
    foreach ($files as $file) {
      include_once $file->uri;
    }
    $new_classes = array_diff(get_declared_classes(), $current_classes);

    // Since we already loaded all the classes manually, we can't test
    // autoloading them in the same page request. So, we run the actual test in
    // a different request instead.
    variable_set('autoloader_test_classes_to_test', $new_classes);
    $this->drupalGet('autoloader-test');
    $xpath = $this->xpath('//div[@id="autoloader-test-result"]');
    $result = (string) current($xpath);
    $this->assertEqual($result, 'Successfully loaded all classes.', $result);
  }
}
