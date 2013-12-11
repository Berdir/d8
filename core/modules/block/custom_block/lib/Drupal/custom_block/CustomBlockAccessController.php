<?php

/**
 * @file
 * Contains \Drupal\custom_block\CustomBlockAccessController.
 */

namespace Drupal\custom_block;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access controller for the custom block entity type.
 */
class CustomBlockAccessController extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation === 'view') {
      return TRUE;
    }
    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
