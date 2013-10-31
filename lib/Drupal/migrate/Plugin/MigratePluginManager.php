<?php

/**
 * @file
 * Contains \Drupal\migrate\MigraterPluginManager.
 */

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\migrate\Entity\MigrationInterface;

/**
 * Manages migrate sources and steps.
 *
 * @see hook_migrate_info_alter()
 */
class MigratePluginManager extends DefaultPluginManager {

  /**
   * Constructs a MigraterPluginManager object.
   *
   * @param string $type
   *   The type of the plugin: row, source, process, destination, id_map.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct("Plugin/migrate/$type", $namespaces, 'Drupal\Component\Annotation\PluginID');
    $this->alterInfo($module_handler, 'migrate_' . $type . '_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'migrate_plugins_' . $type);
  }

  public function createInstance($plugin_id, array $configuration, MigrationInterface $migration = NULL) {
    $plugin_definition = $this->discovery->getDefinition($plugin_id);
    $plugin_class = DefaultFactory::getPluginClass($plugin_id, $plugin_definition);
    return new $plugin_class($configuration, $plugin_id, $plugin_definition, $migration);
  }

}
