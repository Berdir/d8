<?php

/**
 * @file
 * Contains \Drupal\module_test\Controller\ModuleTestController.
 */

namespace Drupal\module_test\Controller;
use Drupal\module_autoload_test\SomeClass;

/**
 * Controller routines for module_test routes.
 */
class ModuleTestController {

  /**
   * Page callback for 'hook dynamic loading' test.
   *
   * If the hook is dynamically loaded correctly, the menu callback should
   * return 'success!'.
   */
  public function hookDynamicLoadingInvoke() {
    $result = \Drupal::moduleHandler()->invoke('module_test', 'test_hook');
    return $result['module_test'];
  }

  /**
   * Page callback for 'hook dynamic loading' test.
   *
   * If the hook is dynamically loaded correctly, the menu callback should
   * return 'success!'.
   */
  public function hookDynamicLoadingInvokeAll() {
    $result = \Drupal::moduleHandler()->invokeAll('test_hook');
    return $result['module_test'];
  }

  /**
   * Page callback for 'class loading' test.
   *
   * This module does not have a dependency on module_autoload_test.module. If
   * that module is enabled, this function should return the string
   * 'Drupal\\module_autoload_test\\SomeClass::testMethod() was invoked.'. If
   * that module is not enabled, this function should return nothing.
   */
  public function testClassLoading() {
    if (class_exists('Drupal\module_autoload_test\SomeClass')) {
      $obj = new SomeClass();
      return ['#markup' => $obj->testMethod()];
    }
  }

}
