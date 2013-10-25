<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateDestinationInterface.
 */

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Row;

interface MigrateDestinationInterface extends PluginInspectionInterface {

  // Note this was formerly static. The EntityAPI implementation in migrate_extras
  // demonstrates the necessity of making this instance-specific.
  public function getIdsSchema();
  // WTF: Review the cases where we need the Migration parameter, can we avoid that?
  public function fields(Migration $migration = NULL);
  // Interaction during import/rollback.
  public function preImport();
  public function preRollback();
  public function postImport();
  public function postRollback();
  // Yes, the classes will vary...
  public function import(Row $row);
  public function rollbackMultiple(array $destination_identifiers);
  // Statistics. Possible WTF - is this the place to do the tracking?
  public function getCreated();
  public function getUpdated();
  public function resetStats();

}
