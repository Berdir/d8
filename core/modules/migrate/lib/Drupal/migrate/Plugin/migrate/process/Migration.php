<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\Migration.
 */


namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Calculates the value of a property based on a previous migration.
 *
 * @MigrateProcessPlugin(
 *   id = "migration"
 * )
 */
class Migration extends ProcessPluginBase implements  ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\migrate\Plugin\MigratePluginManager
   */
  protected $processPluginManager;

  /**
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migrationStorageController;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, MigratePluginManager $process_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrationStorageController = $storage_controller;
    $this->migration = $migration;
    $this->processPluginmanager = $process_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorageController('migration'),
      $container->get('plugin.manager.migrate.process')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    $migration_ids = $this->configuration['migration'];
    if (!is_array($migration_ids)) {
      $migration_ids = array($migration_ids);
    }
    $self = FALSE;
    /** @var \Drupal\migrate\Entity\MigrationInterface[] $migrations */
    $migrations = $this->migrationStorageController->loadMultiple($migration_ids);
    $destination_ids = NULL;
    $source_id_values = array();
    foreach ($migrations as $migration_id => $migration) {
      if ($migration_id == $this->migration->id()) {
        $self = TRUE;
      }
      if (isset($this->configuration['source_ids'][$migration_id])) {
        $configuration = array('source' => $this->configuration['source_ids'][$migration_id]);
        $source_id_values[$migration_id] = $this->processPluginManager
          ->createInstance('get', $configuration, $this->migration)
          ->transform(NULL, $migrate_executable, $row, $destination_property);
      }
      else {
        $source_id_values[$migration_id] = $value;
      }
      // Break out of the loop as soon as a destination ID is found.
      if ($destination_ids = $migration->getIdMap()->lookupDestinationID($source_id_values[$migration_id])) {
        break;
      }
    }
    if (!$destination_ids && (($self && empty($this->configuration['no stub'])) || isset($this->configuration['stub_id'])))  {
      if ($self) {
        $migration = $this->migration;
      }
      else {
        $migration = $migrations[$this->configuration['stub_id']];
      }
      $destination_plugin = $migration->getDestinationPlugin();
      $process = array_intersect_key($migration->get('process'), $destination_plugin->getIds());
      $stub_row = new Row($migration->get('sourceIds'), $source_id_values[$migration->id()]);
      $migrate_executable->processRow($stub_row, $process);
      $destination_ids = $destination_plugin->import($row);
    }
    return $destination_ids;
  }

}
