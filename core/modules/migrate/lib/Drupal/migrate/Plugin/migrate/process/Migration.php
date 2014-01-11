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
    /** @var \Drupal\migrate\Entity\MigrationInterface[] $migrations */
    $migrations = $this->migrationStorageController->loadMultiple($migration_ids);
    $destination_ids = NULL;
    foreach ($migrations as $migration_id => $migration) {
      if (isset($this->configuration['source_ids'][$migration_id])) {
        $configuration = array('source' => $this->configuration['source_ids'][$migration_id]);
        $source_id_value = $this->processPluginManager
          ->createInstance('get', $configuration, $this->migration)
          ->transform(NULL, $migrate_executable, $row, $destination_property);
      }
      else {
        $source_id_value = $value;
      }
      // Break out of the loop as soon as a destination ID is found.
      if ($destination_ids = $migration->getIdMap()->lookupDestinationID($source_id_value)) {
        break;
      }
    }
    // @TODO Add stubbing support.
    return $destination_ids;
  }

}
