<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\PluginCacheClearer.
 */

namespace Drupal\Core\Plugin;
use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines a class which is capable of clearing the cache on plugin managers.
 */
class PluginCacheClearer {

  /**
   * The stored plugin managers.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface[]|\Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface[]
   */
  protected $pluginManagers;

  /**
   * Add a plugin manager to the active list.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   A plugin manager instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown when the plugin manager does not implement the cachedDiscovery
   *   interface.
   */
  public function addPluginManager(PluginManagerInterface $plugin_manager) {
    if (!$plugin_manager instanceof CachedDiscoveryInterface) {
      throw new PluginException('The plugin manager does not implement the CachedDiscovery interface.');
    }
    $this->pluginManagers[] = $plugin_manager;
  }

  /**
   * Clear the cache on all plugin managers.
   */
  public function clearCachedDefinitions() {
    foreach ($this->pluginManagers as $plugin_manager) {
      $plugin_manager->clearCachedDefinitions();
    }
  }

}
