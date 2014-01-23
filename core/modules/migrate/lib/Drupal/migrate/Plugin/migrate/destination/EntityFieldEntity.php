<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFieldEntity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

/**
 * @PluginId("entity_field_entity")
 */
class EntityFieldEntity extends Entity {

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['entity_type']['type'] = 'string';
    $ids['name']['type'] = 'string';
    return $ids;
  }

}
