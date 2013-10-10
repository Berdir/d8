<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\SimpleRow;

interface MigrateProcessInterface {

  public function apply(MigrateRowInterface $row, MigrateExecutable $migrate_executable);

}
