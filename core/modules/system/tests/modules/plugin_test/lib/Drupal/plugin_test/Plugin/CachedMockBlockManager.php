<?php

/**
 * @file
 * Definition of Drupal\plugin_test\Plugin\MockBlockManager.
 */

namespace Drupal\plugin_test\Plugin;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Plugin\Discovery\CacheDecorator;

/**
 * Defines a plugin manager used by Plugin API derivative unit tests.
 */
class CachedMockBlockManager extends MockBlockManager {
  public function __construct() {
    parent::__construct();
    // The CacheDecorator allows us to cache these plugin definitions for
    // quicker retrieval. In this case we are generating a cache key by
    // language.
    $this->discovery = new CacheDecorator($this->discovery, 'mock_block:' . language(LANGUAGE_TYPE_INTERFACE)->langcode);
  }
}
