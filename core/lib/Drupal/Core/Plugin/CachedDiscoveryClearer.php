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
class CachedDiscoveryClearer {

  /**
   * The stored discoveries.
   *
   * @var \Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface[]
   */
  protected $cachedDiscoveries;

  /**
   * Add a plugin manager to the active list.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $cached_discovery
   *   An object that implements the cached discovery interface, typically a
   *   plugin manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown when the passed object does not implement the required
   *   interface.
   */
  public function addCachedDiscovery(CachedDiscoveryInterface $cached_discovery) {
    if (!$cached_discovery instanceof CachedDiscoveryInterface) {
      throw new PluginException('The plugin manager does not implement the CachedDiscovery interface.');
    }
    $this->cachedDiscoveries[] = $cached_discovery;
  }

  /**
   * Clear the cache on all cached discoveries.
   */
  public function clearCachedDefinitions() {
    foreach ($this->cachedDiscoveries as $cached_discovery) {
      $cached_discovery->clearCachedDefinitions();
    }
  }

}
