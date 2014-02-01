<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateProcessPluginManager.
 */

namespace Drupal\migrate\Plugin;

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
  public function createInstance($plugin_id, array $configuration = array(), MigrationInterface $migration = NULL) {
    $index = serialize($configuration);
    if (!isset($this->storage[$migration->id()][$plugin_id][$index])) {
      $this->storage[$migration->id()][$plugin_id][$index] = parent::createInstance($plugin_id, $configuration, $migration);
    }
    return $this->storage[$migration->id()][$plugin_id][$index];
  }


}
