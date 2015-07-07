<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Context\ContextCacheKeys.
 */

namespace Drupal\Core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * A value object to store generated cache keys with its cacheable metadata.
 */
class ContextCacheKeys {

  /**
   * The generated cache keys.
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
   * Gets the generated cache keys.
   *
   * @return string[]
   *   The cache keys.
   */
  public function getKeys() {
    return $this->keys;
  }

  /**
   * Gets the cacheability metadata associated for the optimized cache contexts.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata object.
   */
  public function getCacheableMetadata() {
    return $this->cacheableMetadata;
  }

}
