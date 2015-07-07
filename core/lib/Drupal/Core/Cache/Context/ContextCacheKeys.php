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
class ContextCacheKeys extends CacheableMetadata {

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
   * Constructs a ContextCacheKeys object.
   *
   * @param string[] $keys
   *   The cache context keys.
   */
  public function __construct(array $keys) {
    $this->keys = $keys;
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

}
