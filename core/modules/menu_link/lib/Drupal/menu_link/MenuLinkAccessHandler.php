<?php

/**
 * @file
 * Contains \Drupal\menu_link\MenuLinkAccessHandler.
 */

namespace Drupal\menu_link;

use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access handler for the menu link entity type.
 *
 * @see \Drupal\menu_link\Entity\MenuLink
 */
class MenuLinkAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    $access = $account->hasPermission('administer menu');
    if ($access) {
      switch ($operation) {
        case 'reset':
          // Reset allowed for items defined via hook_menu() and customized.
          return !empty($entity->machine_name) && $entity->customized;

        case 'delete':
          // Only items created by the menu module can be deleted.
          return $entity->module == 'menu' || $entity->updated == 1;

      }
    }
    return $access;
  }

}
