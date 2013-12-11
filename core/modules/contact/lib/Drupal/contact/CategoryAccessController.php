<?php

/**
 * @file
 * Contains \Drupal\contact\CategoryAccessController.
 */

namespace Drupal\contact;

use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
/**
 * Defines an access controller for the contact category entity.
 *
 * @see \Drupal\contact\Entity\Category.
 */
class CategoryAccessController extends EntityAccess {

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
