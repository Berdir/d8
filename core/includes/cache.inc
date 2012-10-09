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
  foreach (cache_backends() as $backend) {
    $backend->invalidateTags($tags);
  }
}

/**
 * Returns a list of cache backends for this site.
 *
 * @return array
 *   An associative array with cache bins as keys, and backend object as the
 *   value.
 */
function cache_backends() {
  $container = drupal_container();
  $cache_services = array_filter($container->getServiceIds(), function ($value) { return substr($value, 0, 6) == 'cache.';});
  $cache_services[] = 'cache';
  $backends = array();
  foreach ($cache_services as $service) {
    $backends[$service] = $container->get($service);
  }
  return $backends;
}