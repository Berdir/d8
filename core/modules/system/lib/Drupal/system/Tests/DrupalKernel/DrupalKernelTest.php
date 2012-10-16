<?php

/**
 * @file
 * Definition of Drupal\system\Tests\DrupalKernel\DrupalKernelTest.
 */

namespace Drupal\system\Tests\DrupalKernel;

use Drupal\Core\DrupalKernel;
use Drupal\simpletest\UnitTestBase;
use ReflectionClass;

/**
 * Test compilation of the DIC.
 */
class DrupalKernelTest extends UnitTestBase {

  public static function getInfo() {
    return array(
      'name' => 'DrupalKernel tests',
      'description' => 'Tests DIC compilation to disk.',
      'group' => 'DrupalKernel',
    );
  }

  /**
   * Test DIC compilation.
   */
  function testCompileDIC() {
    // Because we'll be instantiating a new kernel during this test, the
    // container stored in drupal_container() will be updated as a side effect.
    // We need to be able to restore it to the correct one at the end of this
    // test.
    $original_container = drupal_container();
    global $conf;
    $conf['php_storage']['service_container'] = array(
      'class' => 'Drupal\Component\PhpStorage\MTimeProtectedFileStorage',
      'secret' => $GLOBALS['drupal_hash_salt'],
    );
    $module_enabled = array(
      'system' => 'system',
      'user' => 'user',
    );
    $module_enabled_hash = hash('sha256', implode(',', $module_enabled));
    $system_list = array(
      'module_enabled' => $module_enabled,
      'system_list_hash' => $module_enabled_hash,
    );
    $kernel = new DrupalKernel('testing', FALSE, $system_list);
    $kernel->boot();
    // Instantiate it a second time and we should get the compiled Container
    // class.
    $kernel = new DrupalKernel('testing', FALSE, $system_list);
    $kernel->boot();
    $container = $kernel->getContainer();
    $refClass = new ReflectionClass($container);
    $is_compiled_container =
      $refClass->getParentClass()->getName() == 'Symfony\Component\DependencyInjection\Container' &&
      !$refClass->isSubclassOf('Symfony\Component\DependencyInjection\ContainerBuilder');
    $this->assertTrue($is_compiled_container);

    // Reset the container.
    drupal_container(NULL, TRUE);

    // Now use the read-only storage implementation, simulating a "production"
    // environment.
    drupal_static_reset('drupal_php_storage');
    $conf['php_storage']['service_container'] = array(
      'class' => 'Drupal\Component\PhpStorage\FileReadOnlyStorage',
    );
    $kernel = new DrupalKernel('testing', FALSE, $system_list);
    $kernel->boot();
    $container = $kernel->getContainer();
    $refClass = new ReflectionClass($container);
    $is_compiled_container =
      $refClass->getParentClass()->getName() == 'Symfony\Component\DependencyInjection\Container' &&
      !$refClass->isSubclassOf('Symfony\Component\DependencyInjection\ContainerBuilder');
    $this->assertTrue($is_compiled_container);

    // We make this assertion here purely to show that the new container below
    // is functioning correctly, i.e. we get a brand new ContainerBuilder
    // which has the required new services, after changing the list of enabled
    // modules.
    $this->assertFalse($container->has('bundle_test_class'));

    // Reset the container.
    drupal_container(NULL, TRUE);

    // Add another module so that a different hash is used for the class name
    // and we can test that the new module's bundle is registered to the new
    // container.
    $module_enabled = array(
      'system' => 'system',
      'user' => 'user',
      'bundle_test' => 'bundle_test',
    );
    $module_enabled_hash = hash('sha256', implode(',', array_keys($module_enabled)));
    $system_list = array(
      'module_enabled' => $module_enabled,
      'system_list_hash' => $module_enabled_hash,
    );
    $kernel = new DrupalKernel('testing', FALSE, $system_list);
    $kernel->boot();
    // Instantiate it a second time and we should still get a ContainerBuilder
    // class because we are using the read-only PHP storage.
    $kernel = new DrupalKernel('testing', FALSE, $system_list);
    $kernel->boot();
    $container = $kernel->getContainer();
    $refClass = new ReflectionClass($container);
    $is_container_builder = $refClass->isSubclassOf('Symfony\Component\DependencyInjection\ContainerBuilder');
    $this->assertTrue($is_container_builder);
    // Assert that the new module's bundle was registered to the new container.
    $this->assertTrue($container->has('bundle_test_class'));

    // Restore the original container.
    drupal_container($original_container);
  }
}
