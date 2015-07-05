<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Context\CacheContextKeys.
 */

namespace Drupal\Core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * A value object to store cache context keys with its cacheable metadata.
 */
class CacheContextKeys {

  /**
   * The cache context keys.
   *
   * @var string[]
   */
  protected $keys;

  /**
   * The cacheable metadata associated for the optimized cache contexts.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $cacheableMetadata;

  /**
   * Constructs cache context keys value object.
   *
   * @param string[] $keys
   *   The cache context keys.
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheable_metadata
   *   The cacheable metadata associated for the optimized cache contexts.
   */
  public function __construct(array $keys, CacheableMetadata $cacheable_metadata) {
    $this->keys = $keys;
    $this->cacheableMetadata = $cacheable_metadata;
  }

  /**
   * Gets the cache context keys.
   *
   * @return string[]
   */
  public function getKeys() {
    return $this->keys;
  }

  /**
   * Gets the cacheable metadata associated for the optimized cache contexts.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   */
  public function getCacheableMetadata() {
    return $this->cacheableMetadata;
  }

}
