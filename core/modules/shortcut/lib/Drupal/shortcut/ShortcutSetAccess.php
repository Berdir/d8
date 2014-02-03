<?php

/**
 * @file
 * Contains \Drupal\shortcut\ShortcutSetAccess.
 */

namespace Drupal\shortcut;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the shortcut entity type.
 *
 * @see \Drupal\shortcut\Entity\ShortcutSet
 */
class ShortcutSetAccess extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'update':
        if ($account->hasPermission('administer shortcuts')) {
          return TRUE;
        }
        if ($account->hasPermission('customize shortcut links')) {
          return $entity == shortcut_current_displayed_set($account);
        }
        return FALSE;
        break;

      case 'delete':
        if (!$account->hasPermission('administer shortcuts')) {
          return FALSE;
        }
        return $entity->id() != 'default';
        break;
    }
  }

  /**
  * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $account->hasPermission('administer shortcuts') || $account->hasPermission('customize shortcut links');
  }

}
