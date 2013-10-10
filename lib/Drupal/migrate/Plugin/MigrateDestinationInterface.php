<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateDestinationInterface.
 */

namespace Drupal\migrate\Plugin;

use Drupal\migrate\Entity\Migration;

interface MigrateDestinationInterface {

  public function __construct(array $options);
  // Note this was formerly static. The EntityAPI implementation in migrate_extras
  // demonstrates the necessity of making this instance-specific.
  public function getKeySchema();
  // WTF: Review the cases where we need the Migration parameter, can we avoid that?
  public function fields(Migration $migration = NULL);
  // Interaction during import/rollback.
  public function preImport();
  public function preRollback();
  public function postImport();
  public function postRollback();
  // Yes, the classes will vary...
  public function import(\stdClass $destination_object, \stdClass $source_row);
  public function rollbackMultiple(array $destination_keys);
  // Statistics. Possible WTF - is this the place to do the tracking?
  public function getCreated();
  public function getUpdated();
  public function resetStats();

}
