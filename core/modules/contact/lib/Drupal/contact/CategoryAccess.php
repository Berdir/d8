<?php

/**
 * @file
 * Contains \Drupal\contact\CategoryAccess.
 */

namespace Drupal\contact;

use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the contact category entity type.
 *
 * @see \Drupal\contact\Entity\Category
 */
class CategoryAccess extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'view') {
      // Do not allow access personal category via site-wide route.
      return $account->hasPermission('access site-wide contact form') && $entity->id() !== 'personal';
    }
    elseif ($operation == 'delete' || $operation == 'update') {
      // Do not allow the 'personal' category to be deleted, as it's used for
      // the personal contact form.
      return $account->hasPermission('administer contact forms') && $entity->id() !== 'personal';
    }

    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
