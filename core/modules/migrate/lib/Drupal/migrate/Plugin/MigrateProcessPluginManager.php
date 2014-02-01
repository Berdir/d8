<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateProcessPluginManager.
 */

namespace Drupal\migrate\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\migrate\Entity\MigrationInterface;

class MigrateProcessPluginManager extends MigratePluginManager {

  /**
   * Plugin storage.
   *
   * @var array
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler, $annotation = 'Drupal\migrate\Annotation\MigrateProcessPlugin') {
    parent::__construct($type, $namespaces, $cache_backend, $language_manager, $module_handler, $annotation);
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = array(), MigrationInterface $migration = NULL) {
    $index = serialize($configuration);
    if (!isset($this->storage[$migration->id()][$plugin_id][$index])) {
      $this->storage[$migration->id()][$plugin_id][$index] = parent::createInstance($plugin_id, $configuration, $migration);
    }
    return $this->storage[$migration->id()][$plugin_id][$index];
  }


}
