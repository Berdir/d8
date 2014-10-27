<?php

/**
 * @file
 * Contains \Drupal\rest\Plugin\views\row\DataEntityRow.
 */

namespace Drupal\rest\Plugin\views\row;

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\row\RowPluginBase;

/**
 * Plugin which displays entities as raw data.
 *
 * @ingroup views_row_plugins
 *
 * @ViewsRow(
 *   id = "data_entity",
 *   title = @Translation("Entity"),
 *   help = @Translation("Use entities as row data."),
 *   display_types = {"data"}
 * )
 */
class DataEntityRow extends RowPluginBase {

  /**
   * Overrides \Drupal\views\Plugin\Plugin::$usesOptions.
   */
  protected $usesOptions = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::render().
   */
  public function render($row) {
    $entity = clone $row->_entity;
    // Remove field values the user is not allowed to see.
    foreach ($entity as $field_name => $field) {
      if (!$field->access('view')) {
        unset($entity->{$field_name});
      }
    }
    return $entity;
  }

}
