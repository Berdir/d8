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
      $destination = $new_row->getDestination();
      if (isset($this->configuration['key'])) {
        $process = array('key' => $this->configuration['key']);
        $migrate_executable->processRow($new_row, $process);
        $key = $new_row->getDestinationProperty('key');
      }
      $return[$key] = $destination;
    }
    return $return;
  }
}
