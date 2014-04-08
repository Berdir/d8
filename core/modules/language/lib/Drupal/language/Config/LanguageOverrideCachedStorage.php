<?php

/**
 * @file
 * Contains \Drupal\language\Config\LanguageOverrideFileStorage.
 */

namespace Drupal\language\Config;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\CachedStorage;

/**
 * Defines cached storage for language configuration overrides.
 */
class LanguageOverrideCachedStorage extends CachedStorage implements LanguageOverrideStorageInterface {

  /**
   * The language configuration storage to be cached.
   *
   * @var \Drupal\language\Config\LanguageOverrideStorageInterface
   */
  protected $storage;

  /**
   * Constructs a new LanguageOverrideCachedStorage controller.
   *
   * @param \Drupal\language\Config\LanguageOverrideStorageInterface $storage
   *   A configuration storage controller to be cached.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend instance to use for caching.
   */
  public function __construct(LanguageOverrideStorageInterface $storage, CacheBackendInterface $cache) {
    parent::__construct($storage, $cache);
  }

  /**
   * {@inheritdoc}
   */
  public function setLangcode($langcode) {
    $this->cachePrefix = $langcode;
    $this->storage->setLangcode($langcode);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  function __clone() {
    // Also clone the inner storage, so that language code changes work as
    // expected.
    $this->storage = clone $this->storage;
  }

}
