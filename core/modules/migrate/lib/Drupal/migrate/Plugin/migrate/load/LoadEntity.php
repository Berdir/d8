<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\load\LoadEntityBase.
 */

namespace Drupal\migrate\Plugin\migrate\load;

use Drupal\Component\Utility\MapArray;
use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\SourceEntityInterface;

/**
 * Base class for entity load plugins.
 *
 * @PluginID("d6_entity")
 */
class LoadEntity extends LoadBase {

  /**
   * {@inheritdoc}
   */
  function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $source_plugin = $this->migration->getSourcePlugin();
    if (!$source_plugin instanceof SourceEntityInterface) {
      throw new MigrateException('Migrations with a load plugin using LoadEntity should have an entity as source.');
    }
    if ($source_plugin->bundleMigrationRequired() && empty($configuration['bundle_migration'])) {
      throw new MigrateException(String::format('Source plugin @plugin requires the bundle_migration key to be set.', array('@plugin' => $source_plugin->getPluginId())));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(EntityStorageControllerInterface $storage_controller, array $sub_ids = NULL) {
    $sub_ids_to_load = array();
    // This entity type has no bundles ('user', 'feed', etc).
    if (isset($this->configuration['bundle_migration'])) {
      /** @var \Drupal\migrate\Entity\MigrationInterface $bundle_migration */
      $bundle_migration = $storage_controller->load($this->configuration['bundle_migration']);
      $bundles = array();
      foreach ($bundle_migration->getIdMap() as $key => $row) {
        $key = unserialize($key);
        $bundles[] = $key['sourceid1'];
      }
    }
    else {
      $bundles = array($this->migration->getSourcePlugin()->entityTypeId());
    }
    $sub_ids_to_load = isset($sub_ids) ? array_intersect($bundles, $sub_ids) : $bundles;
    $migrations = array();
    $processed_destinations = array_map(
      function ($value) {
        $parts = explode('.', $value, 2);
        return $parts[0];
      },
      array_keys($this->migration->getProcessPlugins())
    );
    foreach ($sub_ids_to_load as $id) {
      $values = $this->migration->getExportProperties();
      $values['id'] = $this->migration->id() . ':' . $id;
      $values['source']['bundle'] = $id;
      /** @var \Drupal\migrate\Entity\MigrationInterface $migration */
      $migration = $storage_controller->create($values);
      $fields = array_keys($migration->getSourcePlugin()->fields());
      $migration->process += MapArray::copyValuesToKeys(array_diff($fields, $processed_destinations));
      $migrations[$migration->id()] = $migration;
    }

    return $migrations;
  }

}
