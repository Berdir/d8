<?php

/**
 * @file
 * Contains \Drupal\Tests\Plugin\Core\TestDefaultPluginManager.
 */

namespace Drupal\Tests\Core\Plugin;

use Drupal\Component\Plugin\Discovery\StaticDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * A plugin manager for condition plugins.
 */
class TestPluginManager extends DefaultPluginManager {

  /**
   * Constructs aa ConditionManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   */
  public function __construct(\Traversable $namespaces, $definitions) {
    // Create the object that can be used to return definitions for all the
    // plugins available for this type. Most real plugin managers use a richer
    // discovery implementation, but StaticDiscovery lets us add some simple
    // mock plugins for unit testing.
    $this->discovery = new StaticDiscovery();

    // Add the static definitions.
    foreach ($definitions as $key => $definition) {
      $this->discovery->setDefinition($key, $definition);
    }
  }

  /**
   * Set the alter hook name that should be used if needed.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler to invoke the alter hook with.
   * @param string $alter_hook
   *   (optional) Name of the alter hook. Defaults to $owner_$type if not given.
   */
  public function setAlterHook(ModuleHandlerInterface $module_handler, $alter_hook = NULL) {
    $this->moduleHandler = $module_handler;
    $this->alterHook = $alter_hook ? $alter_hook : strtolower($this->subdir);
  }

}
