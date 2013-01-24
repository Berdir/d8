<?php

/**
 * @file
 * Contains Drupal\simpletest\DrupalUnitTestBase.
 */

namespace Drupal\simpletest;

use Drupal\Core\DrupalKernel;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Symfony\Component\DependencyInjection\Reference;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base test case class for Drupal unit tests.
 *
 * Tests extending this base class can access files and the database, but the
 * entire environment is initially empty. Drupal runs in a minimal mocked
 * environment, comparable to the one in the installer or update.php.
 *
 * The module/hook system is functional and operates on a fixed module list.
 * Additional modules needed in a test may be loaded and added to the fixed
 * module list.
 *
 * @see DrupalUnitTestBase::$modules
 * @see DrupalUnitTestBase::enableModules()
 */
abstract class DrupalUnitTestBase extends UnitTestBase {

  /**
   * Modules to enable.
   *
   * Test classes extending this class, and any classes in the hierarchy up to
   * this class, may specify individual lists of modules to enable by setting
   * this property. The values of all properties in all classes in the hierarchy
   * are merged.
   *
   * Unlike UnitTestBase::setUp(), any modules specified in the $modules
   * property are automatically loaded and set as the fixed module list.
   *
   * Unlike WebTestBase::setUp(), the specified modules are loaded only, but not
   * automatically installed. Modules need to be installed manually, if needed.
   *
   * @see DrupalUnitTestBase::enableModules()
   * @see DrupalUnitTestBase::setUp()
   *
   * @var array
   */
  public static $modules = array();

  private $moduleFiles;
  private $themeFiles;
  private $themeData;

  /**
   * A KeyValueMemoryFactory instance to use when building the container.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueMemoryFactory.
   */
  protected $keyValueFactory;

  /**
   * Sets up Drupal unit test environment.
   *
   * @see DrupalUnitTestBase::$modules
   * @see DrupalUnitTestBase
   */
  protected function setUp() {
    // Copy/prime extension file lists once to avoid filesystem scans.
    if (!isset($this->moduleFiles)) {
      $this->moduleFiles = state()->get('system.module.files') ?: array();
      $this->themeFiles = state()->get('system.theme.files') ?: array();
      $this->themeData = state()->get('system.theme.data') ?: array();
    }

    $this->keyValueFactory = new KeyValueMemoryFactory();

    parent::setUp();
    // Build a minimal, partially mocked environment for unit tests.
    $this->containerBuild(drupal_container());
    // Make sure it survives kernel rebuilds.
    $GLOBALS['conf']['container_bundles'][] = 'Drupal\simpletest\TestBundle';

    state()->set('system.module.files', $this->moduleFiles);
    state()->set('system.theme.files', $this->themeFiles);
    state()->set('system.theme.data', $this->themeData);

    // Bootstrap the kernel.
    // No need to dump it; this test runs in-memory.
    $this->kernel = new DrupalKernel('testing', TRUE, drupal_classloader(), FALSE);
    $this->kernel->boot();

    // Collect and set a fixed module list.
    $class = get_class($this);
    $modules = array();
    while ($class) {
      if (property_exists($class, 'modules')) {
        $modules = array_merge($modules, $class::$modules);
      }
      $class = get_parent_class($class);
    }
    $this->enableModules($modules, FALSE);
  }

  /**
   * Sets up the base service container for this test.
   *
   * Extend this method in your test to register additional service overrides
   * that need to persist a DrupalKernel reboot. This method is only called once
   * for each test.
   *
   * @see DrupalUnitTestBase::setUp()
   * @see DrupalUnitTestBase::enableModules()
   */
  public function containerBuild($container) {
    global $conf;
    // Keep the container object around for tests.
    $this->container = $container;

    $container->register('lock', 'Drupal\Core\Lock\NullLockBackend');

    $conf['cache_classes'] = array('cache' => 'Drupal\Core\Cache\MemoryBackend');

    $container
      ->register('config.storage', 'Drupal\Core\Config\FileStorage')
      ->addArgument($this->configDirectories[CONFIG_ACTIVE_DIRECTORY]);

    $conf['keyvalue_default'] = 'keyvalue.memory';
    $container->set('keyvalue.memory', $this->keyValueFactory);
    if (!$container->has('keyvalue')) {
      // TestBase::setUp puts a completely empty container in
      // drupal_container() which is somewhat the mirror of the empty
      // environment being set up. Unit tests need not to waste time with
      // getting a container set up for them. Drupal Unit Tests might just get
      // away with a simple container holding the absolute bare minimum. When
      // a kernel is overridden then there's no need to re-register the keyvalue
      // service but when a test is happy with the superminimal container put
      // together here, it still might a keyvalue storage for anything (for 
      // eg. module_enable) using state() -- that's why a memory service was
      // added in the first place.
      $container
        ->register('keyvalue', 'Drupal\Core\KeyValueStore\KeyValueFactory')
        ->addArgument(new Reference('service_container'));
    }
  }

  /**
   * Installs a specific table from a module schema definition.
   *
   * Use this to install a particular table from System module.
   *
   * @param string $module
   *   The name of the module that defines the table's schema.
   * @param string $table
   *   The name of the table to install.
   */
  protected function installSchema($module, $table) {
    // drupal_get_schema_unprocessed() is technically able to install a schema
    // of a non-enabled module, but its ability to load the module's .install
    // file depends on many other factors. To prevent differences in test
    // behavior and non-reproducible test failures, we only allow the schema of
    // explicitly loaded/enabled modules to be installed.
    if (!$this->container->get('module_handler')->moduleExists($module)) {
      throw new \RuntimeException(format_string("'@module' module is not enabled.", array(
        '@module' => $module,
      )));
    }
    $schema = drupal_get_schema_unprocessed($module, $table);
    if (empty($schema)) {
      throw new \RuntimeException(format_string("Unable to retrieve '@module' module schema for '@table' table.", array(
        '@module' => $module,
        '@table' => $table,
      )));
    }
    Database::getConnection()->schema()->createTable($table, $schema);
    // We need to refresh the schema cache, as any call to drupal_get_schema()
    // would not know of/return the schema otherwise.
    // @todo Refactor Schema API to make this obsolete.
    drupal_get_schema(NULL, TRUE);
  }

  /**
   * Enables modules for this test.
   *
   * Callbacks invoked by module_enable() may need to access information
   * provided by info hooks of the new modules already. However, module_enable()
   * enables the new modules in the system.module configuration only, but that
   * has no effect, since we are operating with a fixed module list.
   *
   * @param array $modules
   *   A list of modules to enable. Dependencies are not resolved; i.e.,
   *   multiple modules have to be specified with dependent modules first.
   * @param bool $install
   *   (optional) Whether to install the list of modules via module_enable().
   *   Defaults to TRUE. If FALSE, the new modules are only added to the fixed
   *   module list and loaded.
   *
   * @todo Remove $install argument and replace all callers that do not pass
   *   FALSE with module_enable().
   */
  protected function enableModules(array $modules, $install = TRUE) {
    if ($install) {
      module_enable($modules, FALSE);
    }
    // Explicitly set the list of modules in the extension handler.
    else {
      $module_handler = $this->container->get('module_handler');
      $module_filenames = $module_handler->getModuleList();
      foreach ($modules as $module) {
        $module_filenames[$module] = drupal_get_filename('module', $module);
      }
      $module_handler->setModuleList($module_filenames);
      $module_handler->resetImplementations();
      $this->kernel->updateModules($module_filenames, $module_filenames);
    }
    // Regardless of loaded or installed, ensure isLoaded() is TRUE in order to
    // make theme() work.
    // Note that the kernel has rebuilt the container; this $module_handler is
    // no longer the $module_handler instance from above.
    $module_handler = $this->container->get('module_handler');
    $module_handler->reload();
  }

}
