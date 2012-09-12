<?php

/**
 * @file
 * Definition of Drupal\comment\CommentStorageController.
 */

namespace Drupal\comment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\DatabaseStorageController;
use LogicException;

/**
 * Defines the controller class for comments.
 *
 * This extends the Drupal\Core\Entity\DatabaseStorageController class, adding
 * required special handling for comment entities.
 */
class CommentStorageController extends DatabaseStorageController {
  /**
   * The thread for which a lock was acquired.
   */
  protected $threadLock = '';


  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::buildQuery().
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);
    // Specify additional fields from the user and node tables.
    $query->innerJoin('node', 'n', 'base.nid = n.nid');
    $query->addField('n', 'type', 'node_type');
    $query->innerJoin('users', 'u', 'base.uid = u.uid');
    $query->addField('u', 'name', 'registered_name');
    $query->fields('u', array('uid', 'signature', 'signature_format', 'picture'));
    return $query;
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::attachLoad().
   */
  protected function attachLoad(&$comments, $load_revision = FALSE) {
    // Set up standard comment properties.
    foreach ($comments as $key => $comment) {
      $comment->name = $comment->uid ? $comment->registered_name : $comment->name;
      $comment->new = node_mark($comment->nid, $comment->changed);
      $comment->node_type = 'comment_node_' . $comment->node_type;
      $comments[$key] = $comment;
    }
    parent::attachLoad($comments, $load_revision);
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::preSave().
   *
   * @see comment_int_to_alphadecimal()
   * @see comment_alphadecimal_to_int()
   */
  protected function preSave(EntityInterface $comment) {
    global $user;

    if (!isset($comment->status)) {
      $comment->status = user_access('skip comment approval') ? COMMENT_PUBLISHED : COMMENT_NOT_PUBLISHED;
    }
    // Make sure we have a proper bundle name.
    if (!isset($comment->node_type)) {
      $node = node_load($comment->nid);
      $comment->node_type = 'comment_node_' . $node->type;
    }
    if (!$comment->cid) {
      // Add the comment to database. This next section builds the thread field.
      // Also see the documentation for comment_view().
      if (!empty($comment->thread)) {
        // Allow calling code to set thread itself.
        $thread = $comment->thread;
      }
      else {
        if ($this->threadLock) {
          // As preSave() is protected, this can only happen when this class
          // is extended in a faulty manner.
          throw new LogicException('preSave is called again without calling postSave() or releaseThreadLock()');
        }
        if ($comment->pid == 0) {
          // This is a comment with no parent comment (depth 0): we start
          // by retrieving the maximum thread level.
          $max = db_query('SELECT MAX(thread) FROM {comment} WHERE nid = :nid', array(':nid' => $comment->nid))->fetchField();
          // Strip the "/" from the end of the thread.
          $max = rtrim($max, '/');
          // We need to get the value at the correct depth.
          $parts = explode('.', $max);
          $n = comment_alphadecimal_to_int($parts[0]);
          $prefix = '';
        }
        else {
          // This is a comment with a parent comment, so increase the part of
          // the thread value at the proper depth.

          // Get the parent comment:
          $parent = comment_load($comment->pid);
          // Strip the "/" from the end of the parent thread.
          $parent->thread = (string) rtrim((string) $parent->thread, '/');
          $prefix = $parent->thread . '.';
          // Get the max value in *this* thread.
          $max = db_query("SELECT MAX(thread) FROM {comment} WHERE thread LIKE :thread AND nid = :nid", array(
            ':thread' => $parent->thread . '.%',
            ':nid' => $comment->nid,
          ))->fetchField();

          if ($max == '') {
            // First child of this parent. As the other two cases do an
            // increment of the thread number before creating the thread
            // string set this to -1 so it requires an increment too.
            $n = -1;
          }
          else {
            // Strip the "/" at the end of the thread.
            $max = rtrim($max, '/');
            // Get the value at the correct depth.
            $parts = explode('.', $max);
            $parent_depth = count(explode('.', $parent->thread));
            $n = comment_alphadecimal_to_int($parts[$parent_depth]);
          }
        }
        // Finally, build the thread field for this new comment. To avoid
        // race conditions, get a lock on the thread. If aother process already
        // has the lock, just move to the next integer.
        do {
          $thread = $prefix . comment_int_to_alphadecimal(++$n) . '/';
        } while (!lock_acquire("comment:$comment->nid:$thread"));
        $this->threadLock = $thread;
      }
      if (empty($comment->created)) {
        $comment->created = REQUEST_TIME;
      }
      if (empty($comment->changed)) {
        $comment->changed = $comment->created;
      }
      // We test the value with '===' because we need to modify anonymous
      // users as well.
      if ($comment->uid === $user->uid && isset($user->name)) {
        $comment->name = $user->name;
      }
      // Add the values which aren't passed into the function.
      $comment->thread = $thread;
      $comment->hostname = ip_address();
    }
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::postSave().
   */
  protected function postSave(EntityInterface $comment, $update) {
    $this->releaseThreadLock();
    // Update the {node_comment_statistics} table prior to executing the hook.
    $this->updateNodeStatistics($comment->nid);
    if ($comment->status == COMMENT_PUBLISHED) {
      module_invoke_all('comment_publish', $comment);
    }
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::postDelete().
   */
  protected function postDelete($comments) {
    // Delete the comments' replies.
    $query = db_select('comment', 'c')
      ->fields('c', array('cid'))
      ->condition('pid', array(array_keys($comments)), 'IN');
    $child_cids = $query->execute()->fetchCol();
    comment_delete_multiple($child_cids);

    foreach ($comments as $comment) {
      $this->updateNodeStatistics($comment->nid);
    }
  }

  /**
   * Updates the comment statistics for a given node.
   *
   * The {node_comment_statistics} table has the following fields:
   * - last_comment_timestamp: The timestamp of the last comment for this node,
   *   or the node created timestamp if no comments exist for the node.
   * - last_comment_name: The name of the anonymous poster for the last comment.
   * - last_comment_uid: The user ID of the poster for the last comment for
   *   this node, or the node author's user ID if no comments exist for the
   *   node.
   * - comment_count: The total number of approved/published comments on this
   *   node.
   *
   * @param $nid
   *   The node ID.
   */
  protected function updateNodeStatistics($nid) {
    // Allow bulk updates and inserts to temporarily disable the
    // maintenance of the {node_comment_statistics} table.
    if (!variable_get('comment_maintain_node_statistics', TRUE)) {
      return;
    }

    $count = db_query('SELECT COUNT(cid) FROM {comment} WHERE nid = :nid AND status = :status', array(
      ':nid' => $nid,
      ':status' => COMMENT_PUBLISHED,
    ))->fetchField();

    if ($count > 0) {
      // Comments exist.
      $last_reply = db_query_range('SELECT cid, name, changed, uid FROM {comment} WHERE nid = :nid AND status = :status ORDER BY cid DESC', 0, 1, array(
        ':nid' => $nid,
        ':status' => COMMENT_PUBLISHED,
      ))->fetchObject();
      db_update('node_comment_statistics')
        ->fields(array(
          'cid' => $last_reply->cid,
          'comment_count' => $count,
          'last_comment_timestamp' => $last_reply->changed,
          'last_comment_name' => $last_reply->uid ? '' : $last_reply->name,
          'last_comment_uid' => $last_reply->uid,
        ))
        ->condition('nid', $nid)
        ->execute();
    }
    else {
      // Comments do not exist.
      $node = db_query('SELECT uid, created FROM {node} WHERE nid = :nid', array(':nid' => $nid))->fetchObject();
      db_update('node_comment_statistics')
        ->fields(array(
          'cid' => 0,
          'comment_count' => 0,
          'last_comment_timestamp' => $node->created,
          'last_comment_name' => '',
          'last_comment_uid' => $node->uid,
        ))
        ->condition('nid', $nid)
        ->execute();
    }
  }

  /**
   * Release the lock acquired for the thread in preSave().
   */
  protected function releaseThreadLock() {
    if ($this->threadLock) {
      lock_release($this->threadLock);
      $this->threadLock = '';
    }
  }
}
