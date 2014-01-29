<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityViewMode.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

/**
 * @PluginID("entity_view_mode")
 */
class EntityViewMode extends EntityConfigBase {

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['targetEntityType']['type'] = 'string';
    $ids['mode']['type'] = 'string';
    return $ids;
  }

}
