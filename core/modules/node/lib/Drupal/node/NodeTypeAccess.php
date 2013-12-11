<?php

/**
 * @file
 * Contains \Drupal\taxonomy\NodeTypeAccess.
 */

namespace Drupal\node;

use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the node type entity.
 *
 * @see \Drupal\node\Entity\NodeType.
 */
class NodeTypeAccess extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'delete' && $entity->isLocked()) {
      return FALSE;
    }
    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
