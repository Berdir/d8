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
 * @see \Drupal\comment\Plugin\Core\Entity\Comment.
 */
class CommentAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return user_access('access comments') && $entity->status->value == COMMENT_PUBLISHED || user_access('administer comments');
        break;

      case 'create':
        return user_access('post comments', $account);
        break;

      case 'update':
        return ($account->uid && $account->uid == $entity->uid->value && $entity->status->value == COMMENT_PUBLISHED && user_access('edit own comments', $account)) || user_access('administer comments', $account);
        break;

      case 'delete':
        return user_access('administer comments', $account);
        break;

      case 'approve':
        return user_access('administer comments', $account);
        break;

      case 'download':
        // Only check access to the parent node for the download operation as
        // we assume that viewing a comment happens on the node page and access
        // for that was checked separately.
        if (user_access('access comments') && $entity->status->value == COMMENT_PUBLISHED || user_access('administer comments')) {
          return node_access('view', $entity->nid->entity);
        }
        return FALSE;
        break;
    }
  }

}
