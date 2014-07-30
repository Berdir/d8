<?php

/**
 * @file
 * Contains \Drupal\user\UserAccessController.
 */

namespace Drupal\user;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access controller for the user entity type.
 */
class UserAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return $this->viewAccess($entity, $langcode, $account);
        break;

      case 'update':
        // Users can always edit their own account. Users with the 'administer
        // users' permission can edit any account except the anonymous account.
        return (($account->id() == $entity->id()) || $account->hasPermission('administer users')) && $entity->id() > 0;
        break;

      case 'delete':
        // Users with 'cancel account' permission can cancel their own account,
        // users with 'administer users' permission can cancel any account
        // except the anonymous account.
        return ((($account->id() == $entity->id()) && $account->hasPermission('cancel account')) || $account->hasPermission('administer users')) && $entity->id() > 0;
        break;
    }
  }

  /**
   * Check view access.
   *
   * See EntityAccessControllerInterface::view() for parameters.
   */
  protected function viewAccess(EntityInterface $entity, $langcode, AccountInterface $account) {
    // Never allow access to view the anonymous user account.
    if ($entity->id()) {
      // Admins can view all, users can view own profiles at all times.
      if ($account->id() == $entity->id() || $account->hasPermission('administer users')) {
        return TRUE;
      }
      elseif ($account->hasPermission('access user profiles')) {
        // Only allow view access if the account is active.
        return $entity->status->value;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    // Fields that are not implicitly allowed to administrative users.
    $explicit_check_fields = array(
      'password',
    );

    // Administrative users are allowed to edit and view all fields.
    if (!in_array($field_definition->getName(), $explicit_check_fields) && $account->hasPermission('administer users')) {
      return TRUE;
    }

    // Flag to indicate if this user entity is the own user account.
    $is_own_account = $items ? $items->getEntity()->id() == $account->id() : FALSE;
    switch ($field_definition->getName()) {
      case 'name':
        // Allow view access to anyone with access to the entity.
        if ($operation == 'view') {
          return TRUE;
        }
        // Allow edit access for the own user name if the permission is
        // satisfied.
        return $is_own_account && $account->hasPermission('change own username');

      case 'preferred_langcode':
      case 'preferred_admin_langcode':
      case 'signature':
      case 'signature_format':
      case 'timezone':
      case 'mail':
        // Allow view access to own mail address and other personalization
        // settings.
        if ($operation == 'view') {
          return $is_own_account;
        }
        // Anyone that can edit the user can also edit this field.
        return TRUE;

      case 'pass':
        // Allow editing the password, but not viewing it.
        return $operation == 'edit';

      case 'created':
        // Allow viewing the created date, but not editing it.
        return $operation == 'view';

      case 'roles':
      case 'status':
      case 'access':
      case 'login':
      case 'init':
        return FALSE;

    }
    // Allow access to all other fields.
    return TRUE;
  }

}
