<?php

/**
 * @file
 * Contains \Drupal\views\ViewAccessHandler.
 */

namespace Drupal\views;

use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the view entity type.
 *
 * @see \Drupal\views\Entity\View
 */
class ViewAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    return $operation == 'view' || parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
