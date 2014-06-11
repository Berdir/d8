<?php

/**
 * @file
 * Contains \Drupal\language\LanguageAccessController.
 */

namespace Drupal\language;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class LanguageAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  public function defaultAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'update':
      case 'delete':
        return !$entity->locked && parent::defaultAccess($entity, $operation, $langcode, $account);
        break;
    }
    return FALSE;
  }

}
