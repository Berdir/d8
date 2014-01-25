<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Comment.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;
use Drupal\migrate\Row;


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
    $query = $this->select('comments', 'c')
      ->fields('c', array('cid', 'pid', 'nid', 'uid', 'subject',
        'comment', 'hostname', 'timestamp', 'status', 'thread', 'name',
        'mail', 'homepage', 'format'));
    $query->orderBy('timestamp');
    return $query;
  }

  public function prepareRow(Row $row, $keep = TRUE) {
    // In D6, status=0 means published, while in D8 means the opposite.
    // See https://drupal.org/node/237636
    $row->setSourceProperty('status', !$row->getSourceProperty('status'));
    return parent::prepareRow($row);
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
      'type' => t("The {node}.type to which this comment is a reply.")
    );
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('comment');
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['cid']['type'] = 'integer';
    return $ids;
  }

}
