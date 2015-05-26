<?php

/**
 * @file
 * Contains \Drupal\system\MenuStorage.
 */

namespace Drupal\system;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a storage for menu entities.
 */
class MenuStorage extends ConfigEntityStorage {

  /**
   * {@inheritdoc}
   *
   * The menu API doesn't require one to use Menu config entities. Hence the
   * Menu config entity should not use config-specific cache tags, but generic
   * ones instead. That's what this code guarantees.
   */
  public function save(EntityInterface $entity) {
    parent::save($entity);
    Cache::invalidateTags($entity->getCacheTags());
  }

}
