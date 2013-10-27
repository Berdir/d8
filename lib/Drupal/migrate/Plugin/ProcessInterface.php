<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateProcessInterface.
 */

namespace Drupal\migrate\Plugin;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Row;

/**
 * An interface for migrate processes.
 */
interface ProcessInterface {

  /**
   * Performs the associated process.
   *
   * @param Row
   *   The row from the source to process.
   * @param MigrateExecutable
   *   The migration in which this process is being executed.
   */
  public function apply(Row $row, MigrateExecutable $migrate_executable);

}
