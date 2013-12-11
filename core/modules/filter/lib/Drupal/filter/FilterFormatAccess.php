<?php

/**
 * @file
 * Contains \Drupal\filter\FilterFormatAccess.
 */

namespace Drupal\filter;

use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the filter format entity type.
 *
 * @see \Drupal\filter\Entity\FilterFormat
 */
class FilterFormatAccess extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    // Handle special cases up front. All users have access to the fallback
    // format.
    if ($operation == 'view' && $entity->isFallbackFormat()) {
      return TRUE;
    }
    // We do not allow filter formats to be deleted through the UI, because that
    // would render any content that uses them unusable.
    if ($operation == 'delete') {
      return FALSE;
    }

    if ($operation != 'view' && $account->hasPermission('administer filters')) {
      return TRUE;
    }

    // Check the permission if one exists; otherwise, we have a non-existent
    // format so we return FALSE.
    $permission = $entity->getPermissionName();
    return !empty($permission) && $account->hasPermission($permission);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $account->hasPermission('administer filters');
  }

}
