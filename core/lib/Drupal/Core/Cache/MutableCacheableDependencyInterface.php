<?php
/**
 * @file
 * Contains \Drupal\Core\Cache\MutableCacheableDependencyInterface.
 */

namespace Drupal\Core\Cache;

/**
 * Allows to add cacheability metadata to an object for the current runtime.
 *
 * This must be used when changing an object in a way that affects its
 * cacheability. For example, when changing the active translation of an entity
 * based on the current content language then a cache context for that must be
 * added.
 */
interface MutableCacheableDependencyInterface extends CacheableDependencyInterface {

  /**
   * Adds cache contexts.
   *
   * @param string[] $cache_contexts
   *   The cache contexts to be added.
   *
   * @return $this
   */
  public function addCacheContexts(array $cache_contexts);

  /**
   * Adds cache tags.
   *
   * @param string[] $cache_tags
   *   The cache tags to be added.
   *
   * @return $this
   */
  public function addCacheTags(array $cache_tags);

  /**
   * Sets the maximum age (in seconds).
   *
   * This only sets the max age if it is lower than the existing one.
   *
   * @param int $max_age
   *   The max age to associate.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   Thrown if a non-integer value is supplied.
   */
  public function setCacheMaxAgeIfLower($max_age);

}
