<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\ValueFromMigration.
 */


namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Calculates the value of a property based on a previous migration.
 *
 * @PluginId("migration")
 */
class Migration extends PluginBase implements MigrateProcessInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storageController = $storage_controller;
    $this->migration = $migration;
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
      $container->get('plugin.manager.entity')->getStorageController('migration')
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
    $migrations = $this->storageController->loadMultiple($migration_ids);

    $properties = $this->configuration['properties'];
    // We want to treat source keys consistently as an array of arrays (each
    // representing one key).
    if (is_array($properties)) {
      if (empty($properties)) {
        // Empty value should return empty results.
        return NULL;
      }
      elseif (is_array(reset($properties))) {
        // Already an array of key arrays, fall through
      }
      else {
        // An array of single-key values - make each one an array
        $new_identifiers = array();
        foreach ($properties as $property) {
          $new_identifiers[] = array($property);
        }
        $properties = $new_identifiers;
      }
    }
    else {
      // A simple value - make it an array within an array
      $properties = array(array($properties));
    }
    $results = array();
    // Each $source_key will be an array of key values
    foreach ($properties as $property) {
      // If any source keys are NULL, skip this set
      $continue = FALSE;
      foreach ($property as $value) {
        if (!isset($value)) {
          $continue = TRUE;
          break;
        }
      }
      if ($continue || empty($property)) {
        continue;
      }
      // Loop through each source migration, checking for an existing dest ID.
      /** @var \Drupal\migrate\Entity\MigrationInterface $migration */
      foreach ($migrations as $migration) {
        // Break out of the loop as soon as a destination ID is found.
        if ($destids = $migration->getIdMap()->lookupDestinationID($value)) {
          if (!empty($destids['destid1'])) {
            break;
          }
        }
      }
      // If no destination ID was found, give each source migration a chance to
      // create a stub.
      if (empty($destids)) {
        foreach ($migrations as $migration) {
          // Is this a self reference?
          if ($migration->id() == $this->migration->id()) {
            if (!array_diff($property, $row->getSourceIdValues())) {
              $destids = array();
              $this->sourceRowStatus = MigrateIdMapInterface::STATUS_NEEDS_UPDATE;
              break;
            }
          }
          // Break out of the loop if a stub was successfully created.
          // @TODO: wtf is this?
          /*
          if ($destids = $migration->createStubWrapper($property, $migration)) {
            break;
          }
          */
        }
      }
      if (!empty($destids)) {
        // Assume that if the destination key is a single value, it
        // should be passed as such
        if (count($destids) == 1) {
          $results[] = reset($destids);
        }
        else {
          $results[] = $destids;
        }
      }
      // If no match found, apply the default value (if any)
      elseif (isset($this->configuration['default'])) {
        $results[] = $this->configuration['default'];
      }
    }
    // Return a single result if we had a single key
    if (count($properties) > 1) {
      return $results;
    }
    else {
      $value = reset($results);
      return empty($value) && $value !== 0 && $value !== '0' ? NULL : $value;
    }

  }

}
