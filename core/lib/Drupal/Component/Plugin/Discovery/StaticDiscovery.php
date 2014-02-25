<?php

/**
 * @file
 * Definition of Drupal\Component\Plugin\Discovery\StaticDiscovery.
 */

namespace Drupal\Component\Plugin\Discovery;

/**
 * A discovery mechanism that allows plugin definitions to be manually
 * registered rather than actively discovered.
 */
class StaticDiscovery extends DiscoveryCachedBase implements DiscoveryInterface {

  /**
   * The array of plugin definitions, keyed by plugin id.
   *
   * @var array
   */
  protected $definitions = array();

  /**
   * Implements Drupal\Component\Plugin\Discovery\DiscoveryInterface::getDefinitions().
   */
  public function getDefinitions() {
    return $this->definitions;
  }

  /**
   * Sets a plugin definition.
   */
  public function setDefinition($plugin, array $definition) {
    $this->definitions[$plugin] = $definition;
  }

  /**
   * Deletes a plugin definition.
   */
  public function deleteDefinition($plugin) {
    unset($this->definitions[$plugin]);
  }
}
