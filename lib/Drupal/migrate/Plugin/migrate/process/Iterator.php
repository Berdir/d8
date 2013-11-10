<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\Iterator.
 */


namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;

/**
 * This plugin iterates and processes an array.
 *
 * @PluginId("iterator")
 */
class Iterator extends PluginBase implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    $row = new Row($value, array());
    $migrate_executable->processRow($row, $this->configuration['process']);
    return $row->getDestination();
  }
}
