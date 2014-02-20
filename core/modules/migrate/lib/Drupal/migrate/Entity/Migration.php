<?php

/**
 * @file
 * Contains \Drupal\migrate\Entity\Migration.
 */

namespace Drupal\migrate\Entity;

use Drupal\Component\Utility\String;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\RequirementsInterface;

/**
 * Defines the Migration entity.
 *
 * The migration entity stores the information about a single migration, like
 * the source, process and destination plugins.
 *
 * @ConfigEntityType(
 *   id = "migration",
 *   label = @Translation("Migration"),
 *   module = "migrate",
 *   controllers = {
 *     "storage" = "Drupal\migrate\MigrationStorageController"
 *   },
 *   config_prefix = "migrate.migration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Migration extends ConfigEntityBase implements MigrationInterface, RequirementsInterface {

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
   * @var array
   */
  public $process;

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
   * The load plugin configuration, if any.
   *
   * @var array
   */
  public $load = array();

  /**
   * The load plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateLoadInterface|false
   */
  protected $loadPlugin = FALSE;

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
   * the values are dependent on the ID map plugin.
   *
   * @var array
   */
  public $sourceIds = array();

  /**
   * The destination identifiers.
   *
   * An array of destination identifiers: the keys are the name of the
   * properties, the values are dependent on the ID map plugin.
   *
   * @var array
   */
  public $destinationIds = FALSE;

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
   * Specify value of source_row_status for current map row. Usually set by
   * MigrateFieldHandler implementations.
   *
   * @var int
   */
  public $sourceRowStatus = MigrateIdMapInterface::STATUS_IMPORTED;

  /**
   * The ratio of the memory limit at which an operation will be interrupted.
   *
   * Can be overridden by a Migration subclass if one would like to push the
   * envelope. Defaults to 0.85.
   *
   * @var float
   */
  protected $memoryThreshold = 0.85;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $highwaterStorage;

  /**
   * @var bool
   */
  public $trackLastImported = FALSE;

  /**
   * The ratio of the time limit at which an operation will be interrupted.
   *
   * Can be overridden by a Migration subclass if one would like to push the
   * envelope. Defaults to 0.9.
   *
   * @var float
   */
  public $timeThreshold = 0.90;

  /**
   * The time limit when executing the migration.
   *
   * @var array
   */
  public $limit = array();

  /**
   * Migration requirements that need to be run before the current one.
   *
   * @var array
   */
  protected $requirements = array();

  /**
   * {@inheritdoc}
   */
  public function getSourcePlugin() {
    if (!isset($this->sourcePlugin)) {
      $this->sourcePlugin = \Drupal::service('plugin.manager.migrate.source')->createInstance($this->source['plugin'], $this->source, $this);
    }
    return $this->sourcePlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessPlugins(array $process = NULL) {
    if (!isset($process)) {
      $process = $this->process;
    }
    $process_plugins = array();
    foreach ($this->getProcessNormalized($process) as $property => $configurations) {
      $process_plugins[$property] = array();
      foreach ($configurations as $configuration) {
        if (isset($configuration['source'])) {
          $process_plugins[$property][] = \Drupal::service('plugin.manager.migrate.process')->createInstance('get', $configuration, $this);
        }
        // Get is already handled.
        if ($configuration['plugin'] != 'get') {
          $process_plugins[$property][] = \Drupal::service('plugin.manager.migrate.process')->createInstance($configuration['plugin'], $configuration, $this);
        }
        if (!$process_plugins[$property]) {
          throw new MigrateException("Invalid process configuration for $property");
        }
      }
    }
    return $process_plugins;
  }

  /**
   * Resolve shorthands into a list of plugin configurations.
   *
   * @param array $process
   *   A process configuration array.
   *
   * @return array
   *   The normalized process configuration.
   */
  protected function getProcessNormalized(array $process) {
    $normalized_configurations = array();
    foreach ($process as $destination => $configuration) {
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
  public function getDestinationPlugin() {
    if (!isset($this->destinationPlugin)) {
      $this->destinationPlugin = \Drupal::service('plugin.manager.migrate.destination')->createInstance($this->destination['plugin'], $this->destination, $this);
    }
    return $this->destinationPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getLoadPlugin() {
    if ($this->load && !$this->loadPlugin) {
      $this->loadPlugin = \Drupal::service('plugin.manager.migrate.load')->createInstance($this->load['plugin'], $this->load, $this);
    }
    return $this->loadPlugin;
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

  /**
   * {@inheritdoc}
   */
  public function getHighwater() {
    return $this->getHighWaterStorage()->get($this->id());
  }

  /**
   * {@inheritdoc}
   */
  public function saveHighwater($highwater) {
    $this->getHighWaterStorage()->set($this->id(), $highwater);
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    // Check whether the current migration source and destination plugin
    // requirements are met or not.
    try {
      if ($this->getSourcePlugin() instanceof RequirementsInterface && !$this->getSourcePlugin()->checkRequirements()) {
        return FALSE;
      }
      if ($this->getDestinationPlugin() instanceof RequirementsInterface && !$this->getDestinationPlugin()->checkRequirements()) {
        return FALSE;
      }

      /** @var \Drupal\migrate\Entity\MigrationInterface[] $required_migrations */
      $required_migrations = entity_load_multiple('migration', $this->requirements);
      // Check if the dependencies are in good shape.
      foreach ($required_migrations as $required_migration) {
        // If the dependent source migration has no IDs then no mappings can
        // be recorded thus it is impossible to see whether the migration ran.
        if (!$required_migration->getSourcePlugin()->getIds()) {
          return FALSE;
        }

        // If the dependent migration has not processed any record, it means the
        // dependency requirements are not met.
        if (!$required_migration->getIdMap()->processedCount()) {
          return FALSE;
        }
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return TRUE;
  }

}
