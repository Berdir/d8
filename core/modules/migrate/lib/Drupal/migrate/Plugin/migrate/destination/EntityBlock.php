<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityBlock.
 */

namespace Drupal\migrate\Plugin\migrate\destination;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * @PluginId("entity_block")
 */
class EntityBlock extends Entity {

  /**
   * {@inheritdoc}
   */
  protected function updateConfigEntity(ConfigEntityInterface $entity, array $parents, $value) {
    if ($parents == array('plugin')) {
      $entity->set('plugin', $value);
    }
    else {
      parent::updateConfigEntity($entity, $parents, $value);
    }
  }

}
