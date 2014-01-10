<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\process\d6\EntityDisplayIdGenerator.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\process\d6;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Row;

/**
 * Generate an entity display Id.
 *
 * @MigrateProcessPlugin(
 *   id = "entity_display_id_generator"
 * )
 */
class EntityDisplayIdGenerator extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   *
   * Create the entity display id based on the entity type, bundle and
   * view mode.
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    return $value[0] . '.' . $value[1] . '.' . $value[2];
  }
}
