<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\PerComponentEntityDisplay.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

/**
 * @MigrateDestinationPlugin(
 *   id = "component_entity_display"
 * )
 */
class PerComponentEntityDisplay extends ComponentEntityDisplayBase {

  const MODE_NAME = 'view_mode';

  /**
   * {@inheritdoc}
   */
  protected function getEntity($entity_type, $bundle, $view_mode) {
    return entity_get_display($entity_type, $bundle, $view_mode);
  }

}
