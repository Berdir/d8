<?php

/**
 * @file
 * Contains \Drupal\comment\CommentAccessController
 */

namespace Drupal\comment;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the comment entity.
 *
 * @see \Drupal\comment\Entity\Comment.
 */
class CommentAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (user_access('access comments') && $entity->status->value == COMMENT_PUBLISHED || user_access('administer comments')) {
          return $entity->nid->entity->access('view');
        }
        return FALSE;
        break;

      case 'update':
        return ($account->id() && $account->id() == $entity->uid->value && $entity->status->value == COMMENT_PUBLISHED && user_access('edit own comments', $account)) || user_access('administer comments', $account);
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
