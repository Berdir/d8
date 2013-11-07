<?php

/**
 * @file
 * Contains \Drupal\migrate\Entity\Migration.
 */

namespace Drupal\migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrateProcessBag;

/**
 * Defines the Migration entity.
 *
 * The migration entity stores the information about a single migration, like
 * the source, process and destination plugins.
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
 *   config_prefix = "migrate.migration",
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
   * The source configuration, with at least a 'plugin' key.
   *
   * Used to initialize the $sourcePlugin.
   *
   * @var array
   */
  public $source;

  /**
   * The source plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  protected $sourcePlugin;

  /**
   * The configuration describing the process plugins.
   *
   * Used to initialize $migrateProcessBag.
   *
   * @var array
   */
  public $process;

  /**
   * The array which stores all active process plugins.
   *
   * @var array
   */
  protected $processPlugins = array();

  /**
   * The destination configuration, with at least a 'plugin' key.
   *
   * Used to initialize $destinationPlugin.
   *
   * @var array
   */
  public $destination;

  /**
   * The destination plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  protected $destinationPlugin;

  /**
   * The identifier map data.
   *
   * Used to initialize $idMapPlugin.
   *
   * @var string
   */
  public $idMap = array();

  /**
   * The identifier map.
   *
   * @var \Drupal\migrate\Plugin\MigrateIdMapInterface
   */
  protected $idMapPlugin;

  /**
   * The source identifiers.
   *
   * An array of source identifiers: the keys are the name of the properties,
   * the values are dependent on the id map plugin.
   *
   * @var array
   */
  public $sourceIds = array();

  /**
   * The destination identifiers.
   *
   * An array of destination identifiers: the keys are the name of the
   * properties, the values are dependent on the id map plugin.
   *
   * @var array
   */
  public $destinationIds = array();

  /**
   * Information on the highwater mark.
   *
   * @var array
   */
  public $highwaterProperty;

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
  public $needsUpdate = MigrateIdMapInterface::STATUS_IMPORTED;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $highwaterStorage;

  /**
   * @var bool
   */
  public $trackLastImported = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    if (!isset($this->sourcePlugin)) {
      $this->sourcePlugin = \Drupal::service('plugin.manager.migrate.source')->createInstance($this->source['plugin'], $this->source, $this);
    }
    return $this->sourcePlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcess() {
    foreach ($this->getProcessNormalized() as $property => $configurations) {
      foreach ($configurations as $configuration) {
        $this->processPlugins[$property] = array();
        if (isset($configuration['source'])) {
          $this->processPlugins[$property][] = \Drupal::service('plugin.manager.migrate.process')->createInstance('get', $configuration, $this);
        }
        // Get is already handled.
        if ($configuration['plugin'] != 'get') {
          $this->processPlugins[$property][] = \Drupal::service('plugin.manager.migrate.process')->createInstance($configuration['plugin'], $configuration, $this);
        }
        if (!$this->processPlugins[$property]) {
          throw new MigrateException("Invalid process configuration for $property");
        }
      }
    }
    return $this->processPlugins;
  }

  /**
   * Resolve shorthands into a list of plugin configurations.
   *
   * @return array
   *   The normalized process configuration.
   */
  protected function getProcessNormalized() {
    $normalized_configurations = array();
    foreach ($this->process as $destination => $configuration) {
      if (is_string($configuration)) {
        $configuration = array(
          'plugin' => 'get',
          'source' => $configuration,
        );
      }
      if (isset($configuration['plugin'])) {
        $configuration = array($configuration);
      }
      $normalized_configurations[$destination] = $configuration;
    }
    return $normalized_configurations;
  }

  /**
   * {@inheritdoc}
   */
  public function getDestination() {
    if (!isset($this->destinationPlugin)) {
      $this->destinationPlugin = \Drupal::service('plugin.manager.migrate.destination')->createInstance($this->destination['plugin'], $this->destination, $this);
    }
    return $this->destinationPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdMap() {
    if (!isset($this->idMapPlugin)) {
      $configuration = $this->idMap;
      $plugin = isset($configuration['plugin']) ? $configuration['plugin'] : 'sql';
      $this->idMapPlugin = \Drupal::service('plugin.manager.migrate.id_map')->createInstance($plugin, $configuration, $this);
    }
    return $this->idMapPlugin;
  }

  /**
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected function getHighWaterStorage() {
    if (!isset($this->highwaterStorage)) {
      $this->highwaterStorage = \Drupal::keyValue('migrate:highwater');
    }
    return $this->highwaterStorage;
  }

  public function getHighwater() {
    return $this->getHighWaterStorage()->get($this->id());
  }

  public function saveHighwater($highwater) {
    $this->getHighWaterStorage()->set($this->id(), $highwater);
  }
}
