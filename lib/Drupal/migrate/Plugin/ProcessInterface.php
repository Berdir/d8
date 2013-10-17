<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Row;

interface MigrateProcessInterface {

  public function apply(Row $row, MigrateExecutable $migrate_executable);

}
