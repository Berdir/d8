<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\process\d6\ViewModeIdGenerator.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\process\d6;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Generate a view mode id.
 *
 * @MigrateProcessPlugin(
 *   id = "view_mode_id_generator"
 * )
 */
class ViewModeIdGenerator extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    // Create the view mode id. $bundle.$view_mode.
    $id = $value[0] . '.' . $value[1];

    // @todo remove this plugin entirely once https://drupal.org/node/2167717
    // and https://drupal.org/node/2169999 are in.
    // For now we skip the row if the view mode exists.
    if (!is_null(entity_load('view_mode', $id))) {
      throw new MigrateSkipRowException();
    }
    return $id;
  }

}
