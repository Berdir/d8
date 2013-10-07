<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateRow;

interface ColumnMappingInterface {

  public function apply(MigrateRow $row, MigrateExecutable $migrate_executable);

}
