<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Cache.
 */

namespace Drupal\Core\Cache;

/**
 * Helper methods for cache.
 */
class Cache {

  /**
   * Indicates that the item should never be removed unless explicitly deleted.
   */
  const PERMANENT = CacheBackendInterface::CACHE_PERMANENT;

  /**
   * Deletes items from all bins with any of the specified tags.
   *
   * Many sites have more than one active cache backend, and each backend may
   * use a different strategy for storing tags against cache items, and
   * deleting cache items associated with a given tag.
   *
   * When deleting a given list of tags, we iterate over each cache backend, and
   * and call deleteTags() on each.
   *
   * @param array $tags
   *   The list of tags to delete cache items for.
   */
  public static function deleteTags(array $tags) {
    foreach (static::getTags() as $tag_service) {
      $tag_service->deleteTags($tags);
    }
  }

  /**
   * Marks cache items from all bins with any of the specified tags as invalid.
   *
   * Many sites have more than one active cache backend, and each backend my use
   * a different strategy for storing tags against cache items, and invalidating
   * cache items associated with a given tag.
   *
   * When invalidating a given list of tags, we iterate over each cache backend,
   * and call invalidateTags() on each.
   *
   * @param array $tags
   *   The list of tags to invalidate cache items for.
   */
  public static function invalidateTags(array $tags) {
    foreach (static::getTags() as $tag_service) {
      $tag_service->invalidateTags($tags);
    }
  }

  /**
   * Gets all cache tag services.
   *
   * @return array
   *  An array of cache tag objects keyed by service name.
   */
  public static function getTags() {
    $tags = array();
    $container = \Drupal::getContainer();
    foreach ($container->getParameter('cache_tags') as $service_id => $tag) {
      $tags[$tag] = $container->get($service_id);
    }

    return $tags;
  }

  /**
   * Gets all cache bin services.
   *
   * @return array
   *  An array of cache bin objects keyed by cache bin.
   */
  public static function getBins() {
    $bins = array();
    $container = \Drupal::getContainer();
    foreach ($container->getParameter('cache_bins') as $service_id => $bin) {
      $bins[$bin] = $container->get($service_id);
    }
    return $bins;
  }

}
