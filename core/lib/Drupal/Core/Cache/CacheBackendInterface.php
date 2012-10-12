<?php

/**
 * @file
 * Definition of Drupal\Core\Cache\CacheBackendInterface.
 */

namespace Drupal\Core\Cache;

/**
 * Defines an interface for cache implementations.
 *
 * All cache implementations have to implement this interface.
 * Drupal\Core\Cache\DatabaseBackend provides the default implementation, which
 * can be consulted as an example. Additionally, a backend needs to accept the
 * cache bin as the first constructor argument, additional arguments can be used
 * as necessary.
 *
 * To make Drupal use your implementation for a certain cache bin, you have to
 * register it accordingly in the dependency injection container. For example,
 * if your implementation of Drupal\Core\Cache\CacheBackendInterface was called
 * Drupal\mymodule\MyCustomCache, the following line would make Drupal use it
 * for the 'page' bin:
 * @code
 *  drupal_classloader_register('mymodule', 'modules/mymodule');
 *  drupal_container()->register('cache.page', 'Drupal\mymodule\MyCustomCache')
 *    ->addArgument('page')
 *    ->addTag('cache');
 * @endcode
 *
 * Additionally, you can register your cache implementation to be used by
 * default for all cache bins. The bins bootstrap, config and page are
 * registered too early to use the default definition and need to be overwritten
 * explicitly.
 * @code
 *  $container = drupal_container();
 *  $container->register('cache', 'Drupal\mymodule\MyCustomCache')
 *    ->addArgument('cache')
 *    ->addTag('cache');
 *  foreach (array('bootstrap', 'config', 'page') as $bin) {
 *    $definition = clone $container->getDefinition('cache');
 *    $container->setDefinition($id, $definition->replaceArgument(0, $bin));
 *  }
 * @endcode
 *
 * Modules that define their own cache bin need to register it in their module
 * bundle using the following code. They also need to define the cache_$bin
 * table required by the default cache implementation.
 *
 * @code
 *  $definition = clone $container->getDefinition('cache');
 *  $container->setDefinition('cache.custom', $definition->replaceArgument(0, 'custom'));
 * @endcode
 *
 * @see cache()
 * @see Drupal\Core\Cache\DatabaseBackend
 */
interface CacheBackendInterface {

  /**
   * Indicates that the item should never be removed unless explicitly selected.
   *
   * The item may be removed using cache()->delete() with a cache ID.
   */
  const CACHE_PERMANENT = 0;

  /**
   * Returns data from the persistent cache.
   *
   * Data may be stored as either plain text or as serialized data. cache_get()
   * will automatically return unserialized objects and arrays.
   *
   * @param $cid
   *   The cache ID of the data to retrieve.
   *
   * @return
   *   The cache or FALSE on failure.
   */
  public function get($cid);

  /**
   * Returns data from the persistent cache when given an array of cache IDs.
   *
   * @param $cids
   *   An array of cache IDs for the data to retrieve. This is passed by
   *   reference, and will have the IDs successfully returned from cache
   *   removed.
   *
   * @return
   *   An array of the items successfully returned from cache indexed by cid.
   */
  public function getMultiple(&$cids);

  /**
   * Stores data in the persistent cache.
   *
   * @param $cid
   *   The cache ID of the data to store.
   * @param $data
   *   The data to store in the cache. Complex data types will be automatically
   *   serialized before insertion.
   *   Strings will be stored as plain text and not serialized.
   * @param $expire
   *   One of the following values:
   *   - CacheBackendInterface::CACHE_PERMANENT: Indicates that the item
   *     should never be removed unless cache->delete($cid) is used explicitly.
   *   - A Unix timestamp: Indicates that the item should be kept at least until
   *     the given time.
   * @param array $tags
   *   An array of tags to be stored with the cache item. These should normally
   *   identify objects used to build the cache item, which should trigger
   *   cache invalidation when updated. For example if a cached item represents
   *   a node, both the node ID and the author's user ID might be passed in as
   *   tags. For example array('node' => array(123), 'user' => array(92)).
   */
  public function set($cid, $data, $expire = CacheBackendInterface::CACHE_PERMANENT, array $tags = array());

  /**
   * Deletes an item from the cache.
   *
   * @param $cid
   *    The cache ID to delete.
   */
  public function delete($cid);

  /**
   * Deletes multiple items from the cache.
   *
   * @param $cids
   *   An array of $cids to delete.
   */
  public function deleteMultiple(Array $cids);

  /**
   * Flushes all cache items in a bin.
   */
  public function flush();

  /**
   * Expires temporary items from the cache.
   */
  public function expire();

  /**
   * Invalidates each tag in the $tags array.
   *
   * @param array $tags
   *   Associative array of tags, in the same format that is passed to
   *   CacheBackendInterface::set().
   *
   * @see CacheBackendInterface::set()
   */
  public function invalidateTags(array $tags);

  /**
   * Performs garbage collection on a cache bin.
   */
  public function garbageCollection();

  /**
   * Checks if a cache bin is empty.
   *
   * A cache bin is considered empty if it does not contain any valid data for
   * any cache ID.
   *
   * @return
   *   TRUE if the cache bin specified is empty.
   */
  public function isEmpty();
}
