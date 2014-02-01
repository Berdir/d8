<?php

/**
 * @file
 * Contains \Drupal\system\DateFormatAccess.
 */

namespace Drupal\system;

use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the date format entity type.
 *
 * @see \Drupal\system\Entity\DateFormat
 */
class DateFormatAccess extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    // There are no restrictions on viewing a date format.
    if ($operation == 'view') {
      return TRUE;
    }
    // Locked date formats cannot be updated or deleted.
    elseif (in_array($operation, array('update', 'delete')) && $entity->isLocked()) {
      return FALSE;
    }

    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
