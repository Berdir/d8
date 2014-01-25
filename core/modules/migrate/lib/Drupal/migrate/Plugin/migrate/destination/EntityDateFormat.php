<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityDateFormat.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * @PluginID("entity_date_format")
 */
class EntityDateFormat extends Entity {

  /**
   * {@inheritdoc}
   */
  protected function updateConfigEntity(ConfigEntityInterface $entity, array $parents, $value) {
    if ($parents[0] == 'pattern') {
      $entity->setPattern($value, $parents[1]);
    }
    else {
      parent::updateConfigEntity($entity, $parents, $value);
    }
  }
}
