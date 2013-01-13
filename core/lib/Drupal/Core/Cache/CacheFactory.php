<?php

/**
 * @file
 * Contains Drupal\Core\Cache\CacheFactory.
 */

namespace Drupal\Core\Cache;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the cache backend factory.
 */
class CacheFactory extends ContainerAware {

  /**
   * Instantiated caches, keyed by bin name.
   *
   * @var array
   */
  protected $bins = array();

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Implements \Symfony\Component\DependencyInjection\ContainerAwareInterface::setContainer().
   */
  function setContainer(ContainerInterface $container = null) {
    $this->container = $container;
  }

  /**
   * Instantiates a cache backend class for a given cache bin.
   *
   * Classes implementing CacheBackendInterface can register themselves both as
   * a default implementation and for specific bins.
   *
   * @param string $bin
   *   The cache bin for which a cache backend object should be returned.
   *
   * @return \Drupal\Core\Cache\CacheBackendInterface
   *   The cache backend object associated with the specified bin.
   */
  public function get($bin) {
    global $conf;
    if (!isset($this->bins[$bin])) {
      if (isset($conf['cache_bin_' . $bin])) {
        $service_name = $conf['cache_bin_' . $bin];
      }
      elseif (isset($conf['cache_bin'])) {
        $service_name = $conf['cache_bin'];
      }
      else {
        $service_name = 'cache.database';
      }
      // Fall back to database if the service does not exist.
      if (!$this->container->has($service_name)) {
        $service_name = 'cache.database';
      }
      $this->bins[$bin] = $this->container->get($service_name)->get($bin);
    }
    return $this->bins[$bin];
  }

  /**
   * Returns a list of cache backends for this site.
   *
   * @return array
   *   An associative array with cache bins as keys, and backend class names as
   *   value.
   */
  public function getBackends() {
    // @todo Improve how cache backend classes are defined. Cannot be
    //   configuration, since e.g. the CachedStorage config storage controller
    //   requires the definition in its constructor already.
    global $conf;
    $cache_backends = isset($conf['cache_classes']) ? $conf['cache_classes'] : array();
    // Ensure there is a default 'cache' bin definition.
    $cache_backends += array('cache' => 'Drupal\Core\Cache\DatabaseBackend');
    return $cache_backends;
  }
}
