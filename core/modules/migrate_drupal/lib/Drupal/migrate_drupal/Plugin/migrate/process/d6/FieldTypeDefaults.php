<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\process\d6\FieldTypeDefaults.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\process\d6;

use Drupal\migrate\MigrateException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Row;

/**
 * Gives us a change to set per field defaults.
 *
 * @MigrateProcessPlugin(
 *   id = "field_type_defaults"
 * )
 */
class FieldTypeDefaults extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    if (is_array($value)) {
      if ($row->getSourceProperty('module') == 'date') {
        $value = 'date_default';
      }
      else {
        throw new MigrateException(sprintf('Lookup failed for %s', var_export($value, TRUE)));
      }
    }
    return $value;
  }

}
