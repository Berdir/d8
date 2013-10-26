<?php

/**
 * @file
 * Contains \Drupal\entity\EntityFormModeList.
 */

namespace Drupal\entity;

/**
 * Provides the listing for entity display modes.
 */
class EntityFormModeList extends EntityDisplayModeList {

  /**
   * Filters entities based on their controllers.
   *
   * @param $entity_type
   *   The entity type of the entity that needs to be validated.
   *
   * @return bool
   *   TRUE if the entity has the correct controller, FALSE if the entity
   *   doesn't has the correct controller.
   */
  protected function isValidEntity($entity_type) {
    return isset($this->entityInfoComplete[$entity_type]['controllers']['form']);
  }

}
