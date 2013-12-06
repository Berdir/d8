<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\MultipleColumnsMap.
 */

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;

/**
 * This plugin changes the current value based on a static lookup map.
 *
 * @PluginId("static_map")
 */
class StaticMap extends PluginBase implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    $map = $this->configuration['map'];
    if (!is_array($value)) {
      $value = array($value);
    }
    if (!$value) {
      throw new MigrateException('Can not lookup without a value.');
    }
    foreach ($value as $key) {
      if (!isset($map[$key])) {
        throw new MigrateException('Lookup failed.');
      }
      $map = $map[$key];
    }
    return $map;
  }

}

