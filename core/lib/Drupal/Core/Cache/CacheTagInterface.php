<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\CacheTagInterface.
 */

namespace Drupal\Core\Cache;

/**
 * Class CacheTagInterface.
 */
interface CacheTagInterface {

  /**
   * Marks specified cache tags as deleted.
   *
   * If the cache items are being deleted because they are no longer "fresh",
   * you may consider using invalidateTags() instead. This allows callers to
   * retrieve the invalid items by calling get() with $allow_invalid set to TRUE.
   * In some cases an invalid item may be acceptable rather than having to
   * rebuild the cache.
   *
   * @param array $tags
   *   Associative array of tags.
   */
  public function deleteTags(array $tags);

  /**
   * Marks specified cache tags as invalid.
   *
   * @param array $tags
   *   Associative array of tags.
   */
  public function invalidateTags(array $tags);

  /**
   * Returns the sum total of validations for a given set of tags.
   *
   * @param array $tags
   *   Associative array of tags.
   * @param bool $set_context
   *   TRUE if called from set context.
   * @return array
   *   Array with two items (invalidations, deletions) providing sum of all
   *   invalidations/deletions over all provided tags.
   */
  public function checksumTags(array $tags, $set_context);

  /**
   * Prepares cache item before it is returned from cache backend interface.
   *
   * $item->deleted should be set to TRUE if item was deleted via tags and
   * $item->valid should be set to FALSE if item was invalidated.
   */
  public function prepareGet(&$item);

  /**
   * Clears cache tag service internal caches.
   */
  public function clearCache();
}
