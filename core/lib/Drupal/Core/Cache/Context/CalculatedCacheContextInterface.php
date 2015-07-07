<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Context\CalculatedCacheContextInterface.
 */

namespace Drupal\Core\Cache\Context;

/**
 * Provides an interface for defining a calculated cache context service.
 */
interface CalculatedCacheContextInterface {

  /**
   * Returns the label of the cache context.
   *
   * @return string
   *   The label of the cache context.
   *
   * @see Cache
   */
  public static function getLabel();

  /**
   * Returns the string representation of the cache context.
   *
   * A cache context service's name is used as a token (placeholder) cache key,
   * and is then replaced with the string returned by this method.
   *
   * @param string|null $parameter
   *   The parameter, or NULL to indicate all possible parameter values.
   *
   * @return string
   *   The string representation of the cache context. When $parameter is NULL,
   *   a value representing all possible parameters must be generated.
   */
  public function getContext($parameter = NULL);

  /**
   * Gets the cacheability metadata for the context based on the parameter value.
   *
   * If the cache context is being optimized away, cache tags and max-age
   * provided by this method will be bubbled up into the cache item.
   *
   * If a max-age of 0 is returned then it means that this context can not
   * be optimized away.
   *
   * @param string|null $parameter
   *   The parameter, or NULL to indicate all possible parameter values.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   A cacheable metadata object.
   */
  public function getCacheableMetadata($parameter = NULL);

}
