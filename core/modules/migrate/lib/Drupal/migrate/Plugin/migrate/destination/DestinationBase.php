<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\DestinationBase.
 */


namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\MigrateDestinationInterface;

abstract class DestinationBase extends PluginBase implements MigrateDestinationInterface {

  /**
   * The migraiton
   *
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;

  /**
   * Constructs an entity destination plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param MigrationInterface $migration
   *   The migration.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
  }

  /**
   * Modify the Row before it is imported.
   */
  public function preImport() {
    // TODO: Implement preImport() method.
  }

  /**
   * Modify the Row before it is rolled back.
   */
  public function preRollback() {
    // TODO: Implement preRollback() method.
  }

  public function postImport() {
    // TODO: Implement postImport() method.
  }

  public function postRollback() {
    // TODO: Implement postRollback() method.
  }

  public function rollbackMultiple(array $destination_identifiers) {
    // TODO: Implement rollbackMultiple() method.
  }

  public function getCreated() {
    // TODO: Implement getCreated() method.
  }

  public function getUpdated() {
    // TODO: Implement getUpdated() method.
  }

  public function resetStats() {
    // TODO: Implement resetStats() method.
  }
}
