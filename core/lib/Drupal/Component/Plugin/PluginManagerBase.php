<?php

/**
 * @file
 * Definition of Drupal\Component\Plugin\PluginManagerBase
 */

namespace Drupal\Component\Plugin;

/**
 * Base class for plugin managers.
 */
abstract class PluginManagerBase implements PluginManagerInterface {

  /**
   * The object that discovers plugins managed by this manager.
   *
   * @var Drupal\Component\Plugin\Discovery\DiscoveryInterface
   */
  protected $discovery;

  /**
   * The object that instantiates plugins managed by this manager.
   *
   * @var Drupal\Component\Plugin\Factory\FactoryInterface
   */
  protected $factory;

  /**
   * The object that returns the preconfigured plugin instance appropriate for a particular runtime condition.
   *
   * @var Drupal\Component\Plugin\Mapper\MapperInterface
   */
  protected $mapper;

  /**
   * A set of defaults to be referenced by $this->processDefinition() if
   * additional processing of plugins is necessary or helpful for development
   * purposes.
   *
   * @var array
   */
  protected $defaults = array();

  /**
   * Implements Drupal\Component\Plugin\PluginManagerInterface::getDefinition().
   */
  public function getDefinition($plugin_id) {
    $definition = $this->discovery->getDefinition($plugin_id);
    if (isset($definition)) {
      $this->processDefinition($definition, $plugin_id);
    }
    return $definition;
  }

  /**
   * Implements Drupal\Component\Plugin\PluginManagerInterface::getDefinitions().
   */
  public function getDefinitions() {
    $definitions = $this->discovery->getDefinitions();
    foreach ($definitions as $plugin_id => &$definition) {
      $this->processDefinition($definition, $plugin_id);
    }

    return $definitions;
  }

  /**
   * Implements Drupal\Component\Plugin\PluginManagerInterface::createInstance().
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    return $this->factory->createInstance($plugin_id, $configuration);
  }

  /**
   * Implements Drupal\Component\Plugin\PluginManagerInterface::getInstance().
   */
  public function getInstance(array $options) {
    return $this->mapper->getInstance($options);
  }

  /**
   * Performs extra processing on plugin definitions.
   *
   * By default we add defaults for the type to the definition. If a type has
   * additional processing logic they can do that by replacing or extending the
   * method.
   */
  protected function processDefinition(&$definition, $plugin_id) {
    $definition += $this->defaults;
  }
}
