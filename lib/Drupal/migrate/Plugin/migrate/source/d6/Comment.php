<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Comment.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;


/**
 * Drupal 6 comment source from database.
 *
 * @PluginId("drupal6_comment")
 */
class Comment extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->database
      ->select('comments', 'c')
      ->fields('c', array('cid', 'pid', 'nid', 'uid', 'subject',
        'comment', 'hostname', 'timestamp', 'status', 'thread', 'name',
        'mail', 'homepage', 'format'));
    $query->join('node', 'n', 'c.nid = n.nid');
    $query->fields('n', array('type'));
    $query->orderBy('timestamp');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'cid' => t('Comment ID.'),
      'pid' => t('Parent comment ID. If set to 0, this comment is not a reply to an existing comment.'),
      'nid' => t('The {node}.nid to which this comment is a reply.'),
      'uid' => t('The {users}.uid who authored the comment. If set to 0, this comment was created by an anonymous user.'),
      'subject' => t('The comment title.'),
      'comment' => t('The comment body.'),
      'hostname' => t("The author's host name."),
      'timestamp' => t('The time that the comment was created, or last edited by its author, as a Unix timestamp.'),
      'status' => t('The published status of a comment. (0 = Published, 1 = Not Published)'),
      'format' => t('The {filter_formats}.format of the comment body.'),
      'thread' => t("The vancode representation of the comment's place in a thread."),
      'name' => t("The comment author's name. Uses {users}.name if the user is logged in, otherwise uses the value typed into the comment form."),
      'mail' => t("The comment author's e-mail address from the comment form, if user is anonymous, and the 'Anonymous users may/must leave their contact information' setting is turned on."),
      'homepage' => t("The comment author's home page address from the comment form, if user is anonymous, and the 'Anonymous users may/must leave their contact information' setting is turned on."),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('comment');
  }

}
