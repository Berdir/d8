<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFile.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\field\FieldInfo;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Row;

/**
 * @MigrateDestinationPlugin(
 *   id = "entity:file"
 * )
 */
class EntityFile extends EntityContentBase {

    public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, array $bundles, MigratePluginManager $plugin_manager, FieldInfo $field_info) {
      $configuration += array(
        'source_base_path' => '',
        'source_path_property' => 'filepath',
        'destination_path_property' => 'filepath',
        'move' => FALSE,
      );
      parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage_controller, $bundles, $plugin_manager, $field_info);
    }

  public function import(Row $row) {
    $source = $this->configuration['source_base_path'] . $row->getSourceProperty($this->configuration['source_path_property']);
    $destination = $row->getDestinationProperty($this->configuration['destination_path_property']);
    $replace = FILE_EXISTS_REPLACE;
    if (!empty($this->configuration['rename'])) {
      $entity_id = $row->getDestinationProperty($this->getKey('id'));
      if (!empty($entity_id) && ($entity = $this->storageController->load($entity_id))) {
        $replace = FILE_EXISTS_RENAME;
      }
    }
    if ($this->configuration['move']) {
      file_unmanaged_move($source, $destination, $replace);
    }
    else {
      file_unmanaged_copy($source, $destination, $replace);
    }
    parent::import($row);
  }

}
