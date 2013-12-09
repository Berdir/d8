<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\Extract.
 */

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;

/**
 * This plugin extract a value from an array.
 *
 * @see https://drupal.org/node/2152731
 *
 * @PluginId("extract")
 */
class Extract extends PluginBase implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      throw new MigrateException('Input should be an array.');
    }
    $new_value = NestedArray::getValue($value, $this->configuration['indexes'], $key_exists);
    if (!$key_exists) {
      throw new MigrateException('Array indexes missing, extraction failed.');
    }
    return $new_value;
  }

}

