<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\load\d6\Node.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\load\d6;

use Drupal\Component\Utility\MapArray;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\migrate\Plugin\migrate\load\LoadBase;

/**
 * @PluginID("d6_node")
 */
class Node extends LoadBase {

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(EntityStorageControllerInterface $storage_controller, array $sub_ids = NULL) {
    /** @var \Drupal\migrate\Entity\MigrationInterface $node_type_migration */
    $node_type_migration = $storage_controller->load('d6_node_type');
    $types = array();
    foreach ($node_type_migration->getIdMap() as $key => $row) {
      $key = unserialize($key);
      $types[] = $key['sourceid1'];
    }
    $ids_to_add = isset($sub_ids) ? array_intersect($types, $sub_ids) : $types;
    $migrations = array();
    foreach ($ids_to_add as $node_type) {
      $values = $this->migration->getExportProperties();
      $values['id'] = 'd6_node:' . $node_type;
      $values['source']['type'] = $node_type;
      /** @var \Drupal\migrate\Entity\MigrationInterface $migration */
      $migration = $storage_controller->create($values);
      $migration->process = MapArray::copyValuesToKeys(array_keys($migration->getSourcePlugin()->fields()));
      $migrations[$migration->id()] = $migration;
    }
    return $migrations;
  }

}
