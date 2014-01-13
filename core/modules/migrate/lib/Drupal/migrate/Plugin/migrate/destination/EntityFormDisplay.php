<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFormDisplay.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Row;

/**
 * @PluginId("entity_form_display")
 */
class EntityFormDisplay extends DestinationBase {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $options = $row->getDestinationProperty('options') ?: array();
    entity_get_form_display($row->getDestinationProperty('entity_type'), $row->getDestinationProperty('bundle'), $row->getDestinationProperty('form_mode'))
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
