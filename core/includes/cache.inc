<?php

use Drupal\Core\DependencyInjection\ContainerBuilder;

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
  foreach (cache_backends() as $name) {
    $container->get($name)->invalidateTags($tags);
  }
}

/**
 * Returns a list of cache backends for this site.
 *
 * @return array
 *   An array of cache backend service names.
 */
function cache_backends() {
  return array_keys(drupal_container()->findTaggedServiceIds('cache'));
}

/**
 * Adds a cache backend to the dependency injection container.
 *
 * The backend is added using the default definition, which is taken from the
 * default cache backend.
 *
 * @param Drupal\Core\DependencyInjection\ContainerBuilder $container
 *   The container to which the backend should be added.
 * @param $bin
 *   Name of the cache bin.
 */
function cache_add_backend(ContainerBuilder $container, $bin) {
  $id = 'cache.' . $bin;
  // Only define the bin if it has not yet been defined.
  if (!$container->has($id)) {
    $definition = clone $container->getDefinition('cache');
    // @todo: Relies on non-enforced constructor argument. Should be replaced
    // with a method call instead.
    $container->setDefinition($id, $definition->replaceArgument(0, $bin));
  }
}