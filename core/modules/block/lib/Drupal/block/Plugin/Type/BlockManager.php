<?php

/**
 * Contains \Drupal\block\Plugin\Type\BlockManager.
 */

namespace Drupal\block\Plugin\Type;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
/**
 * Manages discovery and instantiation of block plugins.
 *
 * @todo Add documentation to this class.
 *
 * @see \Drupal\block\BlockPluginInterface
 */
class BlockManager extends DefaultPluginManager {

  /**
   * Constructs a new \Drupal\block\Plugin\Type\BlockManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct('Block', $namespaces);
    $this->alterHook = 'block';
    $this->moduleHandler = $module_handler;
    $this->setCache($cache, $language_manager, 'block_plugins');
  }
}
