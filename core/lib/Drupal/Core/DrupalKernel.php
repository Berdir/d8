<?php

/**
 * @file
 * Definition of Drupal\Core\DrupalKernel.
 */

namespace Drupal\Core;

use Drupal\Core\CoreBundle;
use Drupal\Component\PhpStorage\PhpStorageInterface;
use Symfony\Component\HttpKernel\Kernel;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * The DrupalKernel class is the core of Drupal itself.
 *
 * This class is responsible for building the Dependency Injection Container and
 * also deals with the registration of bundles. It allows registered bundles to
 * add their services to the container. Core provides the CoreBundle, which adds
 * the services required for all core subsystems. Each module can then add its
 * own bundle, i.e. a subclass of Symfony\Component\HttpKernel\Bundle, to
 * register services to the container.
 */
class DrupalKernel extends Kernel {

  /**
   * Holds the list of enabled modules.
   *
   * @var array
   */
  protected $systemList;

  /**
   * Whether to use a compiled Container as opposed to a ContainerBuilder.
   *
   * @var boolean
   */
  protected $useCompiledContainer;

  /**
   * PHP code storage object to use for the compiled container.
   *
   * @var Drupal\Component\PhpStorage\PhpStorageInterface
   */
  protected $storage;

  /**
   * @todo The first two constructor parameters for the Kernel class are for
   *   environment, e.g. 'prod', 'dev', and a boolean indicating whether it is
   *   in debug mode. Drupal does not currently make use of either of these,
   *   though that may change with http://drupal.org/node/1537198.
   *
   * @param string $environment
   * @param bool $debug
   * @param array $system_list
   *   The same data structure as system_list().
   * @param bool $use_compiled_container
   *   Whether to compile the container to disk or not.
   */
  public function __construct($environment, $debug, array $system_list = NULL, $use_compiled_container = TRUE) {
    parent::__construct($environment, $debug);
    $this->useCompiledContainer = $use_compiled_container;
    $this->storage = drupal_php_storage('service_container');
    if (isset($system_list)) {
      $this->systemList = $system_list;
    }
    else {
      // @todo This is a temporary measure which will no longer be necessary
      //   once we have an ExtensionHandler for managing this list. See
      //   http://drupal.org/node/1331486.
      $this->systemList = system_list();
    }
  }

  /**
   * Overrides Kernel::init().
   */
  public function init() {
    // Intentionally empty. The sole purpose is to not execute Kernel::init(),
    // since that overrides/breaks Drupal's current error handling.
    // @todo Investigate whether it is possible to migrate Drupal's error
    //   handling to the one of Kernel without losing functionality.
  }

  /**
   * Returns an array of available bundles.
   */
  public function registerBundles() {
    $bundles = array(
      new CoreBundle(),
    );

    $modules = $this->systemList['module_enabled'];
    foreach ($modules as $module) {
      $camelized = ContainerBuilder::camelize($module);
      $class = "Drupal\\{$module}\\{$camelized}Bundle";
      if (class_exists($class)) {
        $bundles[] = new $class();
      }
    }
    return $bundles;
  }


  /**
   * Initializes the service container.
   *
   * @todo We are compiling the container and dumping to a PHP file whose name
   *   is based on the list of enabled modules. A new file is created when this
   *   list changes but there is currently no garbage collection in place for
   *   the old files. See http://drupal.org/node/1759582.
   */
  protected function initializeContainer() {
    $this->container = NULL;
    if ($this->useCompiledContainer) {
      // While the default Symfony class name only depends on the environment, for
      // testing purposes we can't use that because there would be a collision as
      // each test method creates a new kernel. On the other hand, the container
      // only depends on the enabled modules (and only on those that provide
      // bundles) so we base the name of the container class on the hash of the
      // enabled modules. We can't directly use the hash though because PHP
      // identifiers always start with a letter and hashes don't so we add a
      // character to the beginning. This mechanism also avoids the problem of
      // needing to rebuild the DIC at the right time on module enable: simply on
      // the next request the hash will change and so the container will be
      // rebuilt.
      $class = 'c' . $this->systemList['system_list_hash'] . ucfirst($this->environment) . ($this->debug ? 'Debug' : '');
      $cache_file = $class . '.php';

      // First, try to load.
      if (!class_exists($class, FALSE)) {
        $this->storage->load($cache_file);
      }
      // If the load succeeded or the class already existed, use it.
      if (class_exists($class, FALSE)) {
        $fully_qualified_class_name = '\\' . $class;
        $this->container = new $fully_qualified_class_name;
      }
    }
    if (!isset($this->container)) {
      $this->container = $this->buildContainer();
      if ($this->useCompiledContainer && !$this->dumpDrupalContainer($cache_file, $this->container, $class, $this->getContainerBaseClass())) {
        // We want to log this as an error but we cannot call watchdog() until
        // the container has been fully built and set in drupal_container().
        $error = 'Container cannot be written to disk';
      }
    }

    $this->container->set('kernel', $this);

    drupal_container($this->container);

    if (isset($error)) {
      watchdog('DrupalKernel', $error);
    }
  }

  /**
   * Builds the service container.
   *
   * @return ContainerBuilder The compiled service container
   */
  protected function buildContainer() {
    $container = $this->getContainerBuilder();

    // Merge in the minimal bootstrap container.
    if ($bootstrap_container = drupal_container()) {
      $container->merge($bootstrap_container);
    }
    foreach ($this->bundles as $bundle) {
      $bundle->build($container);
    }
    $container->compile();
    return $container;
  }

  /**
   * Gets a new ContainerBuilder instance used to build the service container.
   *
   * @return ContainerBuilder
   */
  protected function getContainerBuilder() {
    return new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
  }

  /**
   * Dumps the service container to PHP code in the config directory.
   *
   * This method is based on the dumpContainer method in the parent class, but
   * that method is reliant on the Config component which we do not use here.
   *
   * @param string $cache_file
   *   The full filename to write to.
   * @param ContainerBuilder $container
   *   The service container.
   * @param string $class
   *   The name of the class to generate.
   * @param string $baseClass
   *   The name of the container's base class
   * @param PhpStorageInterface $storage
   *   The PHP storage class.
   *
   * @return bool
   *   TRUE if the container was successfully dumped to disk.
   */
  protected function dumpDrupalContainer($cache_file, ContainerBuilder $container, $class, $baseClass) {
    if (!$this->storage->writeable()) {
      return FALSE;
    }
    // Cache the container.
    $dumper = new PhpDumper($container);
    $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));

    return $this->storage->save($cache_file, $content);
  }

  /**
   * Overrides and eliminates this method from the parent class. Do not use.
   *
   * This method is part of the KernelInterface interface, but takes an object
   * implementing LoaderInterface as its only parameter. This is part of the
   * Config compoment from Symfony, which is not provided by Drupal core.
   *
   * Modules wishing to provide an extension to this class which uses this
   * method are responsible for ensuring the Config component exists.
   */
  public function registerContainerConfiguration(LoaderInterface $loader) {
  }
}
