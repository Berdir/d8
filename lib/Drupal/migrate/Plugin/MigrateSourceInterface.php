<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;

interface MigrateSourceInterface extends \Iterator, \Countable {

  // Returns array representing primary key of current row
  public function getCurrentKey();
  // Returns array of available fields in the source, keys are the field machine names
  // as used in field mappings, values are descriptions.
  public function fields();
  // Statistics. Possible WTF - should the migration class do all tracking?
  public function getIgnored();
  public function getProcessed();
  public function resetStats();
}
