<?php

/**
 * @file
 * Contains \Drupal\shortcut\ShortcutSetAccessHandler.
 */

namespace Drupal\shortcut;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access handler for the shortcut set entity type.
 *
 * @see \Drupal\shortcut\Entity\ShortcutSet
 */
class ShortcutSetAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'update':
        if ($account->hasPermission('administer shortcuts')) {
          return TRUE;
        }
        if (!$account->hasPermission('access shortcuts')) {
          return FALSE;
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
    if ($account->hasPermission('administer shortcuts')) {
      return TRUE;
    }
    if (!$account->hasPermission('access shortcuts')) {
      return FALSE;
    }
    if ($account->hasPermission('customize shortcut links')) {
      return TRUE;
    }
  }

}
