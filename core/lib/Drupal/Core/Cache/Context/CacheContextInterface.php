<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Context\CacheContextInterface.
 */

namespace Drupal\Core\Cache\Context;

/**
 * Provides an interface for defining a cache context service.
 */
interface CacheContextInterface {

  /**
   * Returns the label of the cache context.
   *
   * @return string
   *   The label of the cache context.
   */
  public static function getLabel();

  /**
   * Returns the string representation of the cache context.
   *
   * A cache context service's name is used as a token (placeholder) cache key,
   * and is then replaced with the string returned by this method.
   *
   * @return string
   *   The string representation of the cache context.
   */
  public function getContext();

  /**
   * Gets the cacheable metadata for the context based on the parameter value.
   *
   * If the cache context is being optimized away, cacheable metadata provided
   * by this method will be bubbled up.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata|NULL
   *   A cacheable metadata object or NULL.
   */
  public function getCacheableMetadata();

}
