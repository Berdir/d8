<?php

/**
 * @file
 * Contains \Drupal\views\ViewAccess.
 */

namespace Drupal\views;

use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the view entity type.
 *
 * @see \Drupal\views\Entity\View
 */
class ViewAccess extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    return $operation == 'view' || parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
