<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\MultipleColumnsMap.
 */

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\ProcessInterface;
use Drupal\migrate\Row;

/**
 * This plugin sets a destination property based on multiple sources and a map.
 *
 * @PluginId("multiple_columns_map")
 */
class MultipleColumnsMap extends PluginBase implements ProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function apply(Row $row, MigrateExecutable $migrate_executable) {
    $map = $this->configuration['map'];
    foreach ($this->configuration['properties']['source'] as $property) {
      $map = $map[$row->getSourceProperty($property)];
    }
    $row->setDestinationProperty($this->configuration['properties']['destination'], $map);
  }

}

