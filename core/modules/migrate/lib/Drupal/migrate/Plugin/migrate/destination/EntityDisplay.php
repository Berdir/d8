<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityDisplay.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Row;

/**
 * @PluginId("entity_display")
 */
class EntityDisplay extends DestinationBase {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $options = $row->getDestinationProperty('options') ?: array();
    entity_get_display($row->getDestinationProperty('entity_type'), $row->getDestinationProperty('bundle'), $row->getDestinationProperty('view_mode'))
      ->setComponent($row->getDestinationProperty('field_name'), $options)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getIdsSchema() {
    // TODO: Implement getIdsSchema() method.
  }

  /**
   * {@inheritdoc}
   */
  public function fields(Migration $migration = NULL) {
    // TODO: Implement fields() method.
  }

}
