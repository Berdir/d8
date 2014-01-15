<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\Process\d6\BlockAggregator.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\Process\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "drupal6_block_plugin_id"
 * )
 */
class BlockPluginId extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   *
   * Set the block plugin id.
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    if (is_array($value)) {
      list($module, $delta) = $value;
      switch ($module) {
        case 'aggregator':
          list($type, $id) = explode('-', $delta);
          if ($type == 'category') {
            // @TODO skip row.
            // throw new MigrateSkipRowException();
          }
          $value = 'aggregator_feed_block';
          break;
        case 'menu':
          $value = "system_menu_block:$delta";
          break;
        default:
          // @TODO skip row.
          // throw new MigrateSkipRowException();
      }
    }
    return $value;
  }

}
