<?php

/**
 * @file
 * Contains \Drupal\migrate\Entity\Migration.
 */

namespace Drupal\migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\migrate\Plugin\MigrateProcessBag;
use Drupal\migrate\Plugin\MigrateMapInterface;

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
class Migration extends ConfigEntityBase implements MigrationInterface {

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
   * The plugin ID for the row.
   *
   * @var string
   */
  public $row;

  /**
   * The source keys.
   */
  public $sourceKeys = array();

  /**
   * The source configuration, with at least a 'plugin' key.
   *
   * @var array
   */
  public $source;

  /**
   * @var \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  protected $sourcePlugin;

  /**
   * The configuration describing the process plugins.
   *
   * @var array
   */
  public $process;

  /**
   * @var \Drupal\Migrate\Plugin\MigrateProcessBag
   */
  protected $migrateProcessBag;

  /**
   * The destination configuration, with at least a 'plugin' key.
   *
   * @var array
   */
  public $destination;

  /**
   * @var \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  protected $destinationPlugin;

  /**
   * @var string
   */
  public $idMap = array();

  protected $idMapPlugin;

  /**
   * Indicate whether the primary system of record for this migration is the
   * source, or the destination (Drupal). In the source case, migration of
   * an existing object will completely replace the Drupal object with data from
   * the source side. In the destination case, the existing Drupal object will
   * be loaded, then changes from the source applied; also, rollback will not be
   * supported.
   *
   * @var string
   */
  public $systemOfRecord = self::SOURCE;

  /**
   * Specify value of needs_update for current map row. Usually set by
   * MigrateFieldHandler implementations.
   *
   * @var int
   */
  public $needsUpdate = MigrateMapInterface::STATUS_IMPORTED;

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    if (!isset($this->sourcePlugin)) {
      $this->sourcePlugin = \Drupal::service('plugin.manager.migrate.source')->createInstance($this->source['plugin'], $this->source);
    }
    return $this->sourcePlugin;
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateProcessBag
   */
  public function getProcess() {
    if (!$this->migrateProcessBag) {
      $this->migrateProcessBag = new MigrateProcessBag(\Drupal::service('plugin.manager.migrate.process'), $this->process);
    }
    return $this->migrateProcessBag;
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  public function getDestination() {
    if (!isset($this->destination)) {
      $this->destination = \Drupal::service('plugin.manager.migrate.destination')->createInstance($this->destination['plugin'], $this->destination);
    }
    return $this->destination;
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateIdMapInterface
   */
  public function getIdMap() {
    if (!isset($this->idMapPlugin)) {
      $configuration = $this->idMap;
      $plugin = isset($configuration['plugin']) ? $configuration['plugin'] : 'sql';
      if ($plugin == 'sql' && !isset($configuration['database'])) {
        $configuration['database_service'] = 'database';
      }
      $this->idMapPlugin = \Drupal::service('plugin.manager.migrate.id_map')->createInstance($configuration['plugin'], $configuration);
    }
    return $this->idMapPlugin;
  }
}
