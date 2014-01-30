<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityDateFormat.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;

/**
 * @MigrateDestinationPlugin(
 *   id = "entity:date_format"
 * )
 */
class EntityDateFormat extends EntityConfigBase {

  /**
   * {@inheritdoc}
   */
  protected function updateEntityProperty(EntityInterface $entity, array $parents, $value) {
    /** @var \Drupal\system\DateFormatInterface $entity */
    if ($parents[0] == 'pattern') {
      $entity->setPattern($value, $parents[1]);
    }
    else {
      parent::updateEntityProperty($entity, $parents, $value);
    }
  }
}
