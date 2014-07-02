<?php

/**
 * @file
 * Contains \Drupal\field\FieldInstanceConfigAccessHandler.
 */

namespace Drupal\field;

use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access handler for the field instance entity type.
 *
 * @see \Drupal\field\Entity\FieldInstanceConfig
 */
class FieldInstanceConfigAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'delete' && $entity->getFieldStorageDefinition()->isLocked()) {
      return FALSE;
    }
    return $account->hasPermission('administer ' . $entity->entity_type . ' fields');
  }

}
