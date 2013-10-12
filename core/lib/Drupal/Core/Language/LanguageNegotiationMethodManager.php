<?php

/**
 * @file
 * Contains \Drupal\Core\Language\LanguageNegotiationMethodManager.
 */

namespace Drupal\Core\Language;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages language negotiation methods.
 */
class LanguageNegotiationMethodManager extends DefaultPluginManager {

  /**
   * Constructs a new LanguageNegotiationMethodManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler = NULL) {
    parent::__construct('Plugin/LanguageNegotiation', $namespaces);
    $this->cacheBackend = $cache_backend;
    $this->cacheKeyPrefix = 'language_negotiation_plugins';
    $this->cacheKey = 'language_negotiation_plugins';
    if ($module_handler) {
      $this->alterInfo($module_handler, 'language_negotiation_info');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    $this->definitions = NULL;
    $this->cacheBackend->delete($this->cacheKey);
  }

}
