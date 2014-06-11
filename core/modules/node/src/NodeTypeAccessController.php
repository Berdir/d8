<?php

/**
 * @file
 * Contains \Drupal\taxonomy\NodeTypeAccessController.
 */

namespace Drupal\node;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the node type entity.
 *
 * @see \Drupal\node\Entity\NodeType.
 */
class NodeTypeAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function defaultAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'delete' && $entity->isLocked()) {
      return FALSE;
    }
    return parent::defaultAccess($entity, $operation, $langcode, $account);
  }

}
