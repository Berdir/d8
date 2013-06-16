<?php

/**
 * @file
 * Contains \Drupal\views\ViewAccessController.
 */

namespace Drupal\views;

use Drupal\user\Plugin\Core\Entity\User;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the access controller for the view entity type.
 */
class ViewAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = LANGUAGE_DEFAULT, User $account = NULL) {
    return $operation == 'view' || user_access('administer views', $account);
  }

}
