<?php

/**
 * @file
 * Contains \Drupal\system\MenuAccess.
 */

namespace Drupal\system;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the menu entity type.
 *
 * @see \Drupal\system\Entity\Menu
 */
class MenuAccess extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation === 'view') {
      return TRUE;
    }
    // Locked menus could not be deleted.
    elseif ($operation == 'delete' && $entity->isLocked()) {
      return FALSE;
    }

    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
