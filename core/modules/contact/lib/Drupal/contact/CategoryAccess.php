<?php

/**
 * @file
 * Contains \Drupal\contact\CategoryAccess.
 */

namespace Drupal\contact;

use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the contact category entity type.
 *
 * @see \Drupal\contact\Entity\Category
 */
class CategoryAccess extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'delete' || $operation == 'update') {
      // Do not allow delete 'personal' category used for personal contact form.
      return user_access('administer contact forms', $account) && $entity->id() !== 'personal';
    }
    else {
      return user_access('administer contact forms', $account);
    }
  }

}
