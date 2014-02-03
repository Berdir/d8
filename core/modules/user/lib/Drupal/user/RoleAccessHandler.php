<?php

/**
 * @file
 * Contains \Drupal\user\RoleAccessHandler.
 */

namespace Drupal\user;

use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the user_role entity type.
 *
 * @see \Drupal\user\Entity\Role
 */
class RoleAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'delete':
        if ($entity->id() == DRUPAL_ANONYMOUS_RID || $entity->id() == DRUPAL_AUTHENTICATED_RID) {
          return FALSE;
        }

      default:
        return parent::checkAccess($entity, $operation, $langcode, $account);
    }
  }

}
