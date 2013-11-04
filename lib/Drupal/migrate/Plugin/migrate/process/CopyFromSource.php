<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\CopyFromSource.
 */

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\ProcessInterface;
use Drupal\migrate\Row;

/**
 * This plugin copies from the source to the destination.
 *
 * @PluginId("copy_from_source")
 */
class CopyFromSource extends PluginBase implements ProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function apply(Row $row, MigrateExecutable $migrate_executable) {
    foreach ($this->configuration as $from => $to) {
      if ($row->hasSourceProperty($from)) {
        $destination_values = $row->getSourceProperty($from);
        $row->setDestinationPropertyDeep($to, $destination_values);
      }
    }
  }

}
