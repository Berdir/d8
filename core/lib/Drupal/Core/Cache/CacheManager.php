<?php

/**
 * Definition of \Drupal\Cache\CacheManager.
 */

namespace Drupal\Cache;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Cache manager.
 */
class CacheManager implements ContainerAwareInterface {

  /**
   * @var Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Implements Symfony\Component\DependencyInjection\ContainerAwareInterface::setContainer()
   */
  public function setContainer(ContainerInterface $container = null) {
    $this->container = $container;
  }

  /**
   * Get all bin names.
   *
   * @return array
   *   Array of bin strings. 
   */
  public function getAllBins() {
    return $this->container->getParameter('cache.available-backends');
  }

  /**
   * Invalidate given tags in all bins.
   *
   * @param array $tags
   *   Array of tags.
   */
  public function invalidateTags(array $tags) {
    foreach ($this->getAllBins() as $bin) {
      if ($bin !== 'cache') {
        $bin = 'cache.' . $bin;
      }
      $this->container->get($bin)->invalidateTags($tags);
    }
  }

  /**
   * Flush all bins.
   */
  public function flush() {
    foreach ($this->getAllBins() as $bin) {
      if ($bin !== 'cache') {
        $bin = 'cache.' . $bin;
      }
      $this->container->get($bin)->flush();
    }
  }

}
