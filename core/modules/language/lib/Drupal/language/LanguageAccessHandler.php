<?php

/**
 * @file
 * Contains \Drupal\language\LanguageAccessHandler.
 */

namespace Drupal\language;

use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access handler for the language entity type.
 *
 * @see \Drupal\language\Entity\Language
 */
class LanguageAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'update':
      case 'delete':
        return !$entity->locked && parent::checkAccess($entity, $operation, $langcode, $account);
        break;
    }
    return FALSE;
  }

}
