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
    $return = array();
    foreach ($value as $key => $new_value) {
      $new_row = new Row($new_value, array());
      $migrate_executable->processRow($new_row, $this->configuration['process']);
      $return[$key] = $new_row->getDestination();
    }
    return $return;
  }
}
