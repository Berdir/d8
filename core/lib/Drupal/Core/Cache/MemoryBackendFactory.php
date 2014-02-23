<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\MemoryBackendFactory.
 */

namespace Drupal\Core\Cache;

class MemoryBackendFactory implements CacheFactoryInterface {

  /**
   * The cache tag factory service.
   *
   * @var \Drupal\Core\Cache\CacheTagFactory
   */
  protected $cacheTagFactory;

  /**
   * Constructs the DatabaseBackendFactory object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   */
  function __construct(CacheTagFactory $cache_tag_factory) {
    $this->cacheTagFactory = $cache_tag_factory;
  }

  /**
   * {@inheritdoc}
   */
  function get($bin) {
    return new MemoryBackend($this->cacheTagFactory->get(), $bin);
  }

}
