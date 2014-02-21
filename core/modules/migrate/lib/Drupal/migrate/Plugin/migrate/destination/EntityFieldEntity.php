<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFieldEntity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

/**
 * @MigrateDestination(
 *   id = "entity:field_entity"
 * )
 */
class EntityFieldEntity extends EntityConfigBase {

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['entity_type']['type'] = 'string';
    $ids['name']['type'] = 'string';
    return $ids;
  }

}
