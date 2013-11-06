<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\CopyFromSource.
 */

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;

/**
 * This plugin copies from the source to the destination.
 *
 * @PluginId("get")
 */
class Get extends PluginBase implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    $source = $this->configuration['source'];
    $properties = is_string($source) ? array($source) : $source;
    $return = array();
    foreach ($properties as $property) {
      if (empty($property)) {
        $return[] = $value;
      }
      elseif ($property[0] == '@') {
        // This either references a destination property.
        if (preg_match('/^@(@@)*\w', $property)) {
          // Which might contain @ characters.
          $property = str_replace('@@', '@', substr($property, 1));
          $return[] = $row->getDestinationProperty($property);
        }
        else {
          // Or a source which might also contain @ characters.
          $property = str_replace('@@', '@', $property);
          $return[] = $row->getSourceProperty($property);
        }
      }
      else {
        $return[] = $row->getSourceProperty($property);
      }
    }
    return is_string($source) ? $return[0] : $return;
  }
}
