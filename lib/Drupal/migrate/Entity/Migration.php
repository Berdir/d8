<?php

/**
 * @file
 * Contains \Drupal\migrate\Entity\Migration.
 */

namespace Drupal\migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Migration entity.
 *
 * @EntityType(
 *   id = "migration",
 *   label = @Translation("Migration"),
 *   module = "migrate",
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "list" = "Drupal\Core\Config\Entity\DraggableListController",
 *     "access" = "Drupal\Core\Entity\EntityAccessController",
 *     "form" = {
 *       "add" = "Drupal\Core\Entity\EntityFormController",
 *       "edit" = "Drupal\Core\Entity\EntityFormController",
 *       "delete" = "Drupal\Core\Entity\EntityFormController"
 *     }
 *   },
 *   config_prefix = "migration.entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "admin/config/migration/{migration_entity}"
 *   }
 * )
 */
class Migration extends ConfigEntityBase  {

  /**
   * The migration ID (machine name).
   *
   * @var string
   */
  public $id;

  /**
   * The migration UUID.
   *
   * This is assigned automatically when the migration is created.
   *
   * @var string
   */
  public $uuid;

  /**
   * The human-readable label for the migration.
   *
   * @var string
   */
  public $label;

  /**
   * The source plugin id.
   *
   * @var string
   */
  protected $source_id;

  /**
   * The source configuration.
   *
   * @var array
   */
  protected $source_configuration;

  /**
   * @var \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  protected $source;

  /**
   * The map plugin id.
   *
   * @var string
   */
  protected $map_id;

  /**
   * The map configuration.
   *
   * @var array
   */
  protected $map_configuration;

  /**
   * @var \Drupal\migrate\Plugin\MigrateMapInterface
   */
  protected $map;

  /**
   * The destination plugin id.
   *
   * @var string
   */
  protected $destination_id;

  /**
   * The destination configuration.
   *
   * @var array
   */
  protected $destination_configuration;

  /**
   * @var \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  protected $destination;

  /**
   * {@inheritdoc}
   */
  public function getExportProperties() {
    $properties = parent::getExportProperties();
    foreach (array('source', 'map', 'destination') as $prefix) {
      foreach (array('id', 'configuration') as $postfix) {
        $property = $prefix . '_' . $postfix;
        $properties[$property] = $this->$property;
      }
    }
    return $properties;
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  public function getSource() {
    if (!isset($this->source)) {
      $this->source = \Drupal::service('plugin.manager.migrate.source')->createInstance($this->source_id, $this->source_configuration);
    }
    return $this->source;
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateMapInterface
   */
  public function getMap() {
    if (!isset($this->map)) {
      $this->map = \Drupal::service('plugin.manager.migrate.map')->createInstance($this->map_id, $this->map_configuration);
    }
    return $this->map;
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  public function getDestination() {
    if (!isset($this->destination)) {
      $this->destination = \Drupal::service('plugin.manager.migrate.destination')->createInstance($this->destination_id, $this->destination_configuration);
    }
    return $this->destination;
  }

}
