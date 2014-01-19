<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityDisplay.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Entity\MigrationInterface;
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
    $entity = $this->getEntity($row->getDestinationProperty('entity_type'), $row->getDestinationProperty('bundle'), $row->getDestinationProperty('view_mode'));
    $entity->setComponent($row->getDestinationProperty('field_name'), $options)->save();
    return array($entity->id());
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
  public function fields(MigrationInterface $migration = NULL) {
    // TODO: Implement fields() method.
  }

  /**
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected function getEntity($entity_type, $bundle, $view_mode) {
    return entity_get_display($entity_type, $bundle, $view_mode);
  }

}
