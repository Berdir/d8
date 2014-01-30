<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityDisplay.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

/**
 * @MigrateDestinationPlugin(
 *   id = "entity:entity_display"
 * )
 */
class EntityDisplay extends EntityDisplayBase {

  const MODE_NAME = 'view_mode';

  /**
   * {@inheritdoc}
   */
  protected function getEntity($entity_type, $bundle, $view_mode) {
    return entity_get_display($entity_type, $bundle, $view_mode);
  }

}
