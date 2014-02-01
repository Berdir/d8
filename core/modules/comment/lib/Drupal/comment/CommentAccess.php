<?php

/**
 * @file
 * Contains \Drupal\comment\CommentAccess
 */

namespace Drupal\comment;

use Drupal\Core\Entity\EntityAccess;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access class for the comment entity type.
 *
 * @see \Drupal\comment\Entity\Comment
 */
class CommentAccess extends EntityAccess {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return user_access('access comments', $account);
        break;

      case 'update':
        return ($account->id() && $account->id() == $entity->uid->value && $entity->status->value == CommentInterface::PUBLISHED && user_access('edit own comments', $account)) || user_access('administer comments', $account);
        break;

      case 'delete':
        return user_access('administer comments', $account);
        break;

      case 'approve':
        return user_access('administer comments', $account);
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return user_access('post comments', $account);
  }

}
