<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;

use Drupal\migrate\MigrateExecutable;

interface MigrateProcessInterface {

  public function apply(MigrateRowInterface $row, MigrateExecutable $migrate_executable);

}
