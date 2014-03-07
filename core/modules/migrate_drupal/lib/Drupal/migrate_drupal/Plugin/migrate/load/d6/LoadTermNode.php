<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\load\d6\TermNode.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\load\d6;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\load\LoadEntity;

/**
 * @PluginID("d6_term_node")
 */
class LoadTermNode extends LoadEntity {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    $configuration['bundle_migration'] = 'd6_taxonomy_vocabulary';
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(EntityStorageControllerInterface $storage_controller, array $sub_ids = NULL) {
    /** @var \Drupal\migrate\Entity\MigrationInterface $bundle_migration */
    $bundle_migration = $storage_controller->load('d6_taxonomy_vocabulary');
    $migrate_executable = new MigrateExecutable($bundle_migration, new MigrateMessage());
    $process = array_intersect_key($bundle_migration->get('process'), $bundle_migration->getDestinationPlugin()->getIds());
    $migrations = array();
    foreach ($bundle_migration->getSourcePlugin()->getIterator() as $source_row) {
      $row = new Row($source_row, $source_row);
      $migrate_executable->processRow($row, $process);
      $new_vid = $row->getDestinationProperty('vid');
      $old_vid = $source_row['vid'];
      $values = $this->migration->getExportProperties();
      $migration_id = $this->migration->id() . ':' . $old_vid;
      $values['id'] = $migration_id;
      $values['source']['vid'] = $old_vid;
      $values['process'][$new_vid] = 'tid';
      $migrations[$migration_id] = $storage_controller->create($values);;
    }

    return $migrations;
  }

}
