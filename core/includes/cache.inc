<?php

/**
 * @file
 * Functions and interfaces for cache handling.
 */

/**
 * Returns a cache service.
 *
 * @param $bin
 *   The cache bin for which the cache object should be returned, defaults to
 *   'cache'.
 *
 * @return Drupal\Core\Cache\CacheBackendInterface
 *   The cache object associated with the specified bin.
 *
 * @see Drupal\Core\Cache\CacheBackendInterface
 */
function cache($bin = 'cache') {
  $service = $bin == 'cache' ? 'cache' : 'cache.' . $bin;
  return drupal_container()->get($service);
}

/**
 * Invalidates the items associated with given list of tags.
 *
 * Many sites have more than one active cache backend, and each backend my use
 * a different strategy for storing tags against cache items, and invalidating
 * cache items associated with a given tag.
 *
 * When invalidating a given list of tags, we iterate over each cache backend,
 * and call invalidate on each.
 *
 * @param array $tags
 *   The list of tags to invalidate cache items for.
 */
function cache_invalidate(array $tags) {
  $container = drupal_container();
  // @todo: This is method is only available on the ContainerBuilder and needs
  // to be moved to a compiler pass once the container is compiled to disk.
  foreach (array_keys($container->findTaggedServiceIds('cache')) as $name) {
    $container->get($name)->invalidateTags($tags);
  }
}
