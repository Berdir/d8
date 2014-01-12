<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFieldInstance.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Row;

/**
 * @PluginId("entity_field_instance")
 */
class EntityFieldInstance extends Entity {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $row->setDestinationProperty('id', implode('.', array(
      $row->getDestinationProperty('entity_type'),
      $row->getDestinationProperty('bundle'),
      $row->getDestinationProperty('field_name'),
    )));
    return parent::import($row);
  }

}
