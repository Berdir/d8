<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFormDisplay.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

/**
 * @PluginId("entity_form_display")
 */
class EntityFormDisplay extends EntityDisplayBase {

  const MODE_NAME = 'view_mode';

  /**
   * {@inheritdoc}
   */
  protected function getEntity($entity_type, $bundle, $form_mode) {
    return entity_get_form_display($entity_type, $bundle, $form_mode);
  }

}
