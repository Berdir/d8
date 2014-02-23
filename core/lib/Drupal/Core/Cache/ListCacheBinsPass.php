<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\ListCacheBinsPass.
 */

namespace Drupal\Core\Cache;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds cache_bins and cache_tags parameters to the container.
 */
class ListCacheBinsPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    foreach (array('cache.bin' => 'cache_bins', 'cache.tag' => 'cache_tags') as $name => $param) {
      $params = array();
      foreach ($container->findTaggedServiceIds($name) as $id => $attributes) {
        $params[$id] = substr($id, strpos($id, '.') + 1);
      }
      $container->setParameter($param, $params);
    }
  }
}
