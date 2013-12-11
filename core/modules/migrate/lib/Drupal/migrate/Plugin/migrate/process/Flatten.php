<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\Flatten.
 */

namespace Drupal\migrate\Plugin\migrate\process;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin flattens the current value.
 *
 * @MigrateProcessPlugin(
 *   id = "flatten",
 *   handle_multiples = TRUE
 * )
 */
class Flatten extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    return iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($value)), FALSE);
  }
}
