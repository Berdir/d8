<?php

/**
 * @file
 * Definition of Drupal\node\Node.
 */

namespace Drupal\node;

use Drupal\entity\ContentEntityInterface;
use Drupal\entity\Entity;

/**
 * Defines the node entity class.
 */
class Node extends Entity implements ContentEntityInterface {

  /**
   * The node ID.
   *
   * @var integer
   */
  public $nid;

  /**
   * The node revision ID.
   *
   * @var integer
   */
  public $vid;

  /**
   * The node UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The node content type (bundle).
   *
   * @var string
   */
  public $type;

  /**
   * The node language code.
   *
   * @var string
   */
  public $langcode = LANGUAGE_NOT_SPECIFIED;

  /**
   * The node title.
   *
   * @var string
   */
  public $title;

  /**
   * The node owner's user ID.
   *
   * @var integer
   */
  public $uid;

  /**
   * The node published status indicator.
   *
   * Unpublished nodes are only visible to their authors and to administrators.
   * The value is either NODE_PUBLISHED or NODE_NOT_PUBLISHED.
   *
   * @var integer
   */
  public $status;

  /**
   * The node creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The node modification timestamp.
   *
   * @var integer
   */
  public $changed;

  /**
   * The node comment status indicator.
   *
   * COMMENT_NODE_HIDDEN => no comments
   * COMMENT_NODE_CLOSED => comments are read-only
   * COMMENT_NODE_OPEN => open (read/write)
   *
   * @var integer
   */
  public $comment;

  /**
   * The node promotion status.
   *
   * Promoted nodes should be displayed on the front page of the site. The
   * value is either NODE_PROMOTED or NODE_NOT_PROMOTED.
   *
   * @var integer
   */
  public $promote;

  /**
   * The node sticky status.
   *
   * Sticky nodes should be displayed at the top of lists in which they appear.
   * The value is either NODE_STICKY or NODE_NOT_STICKY.
   *
   * @var integer
   */
  public $sticky;

  /**
   * The node translation set ID.
   *
   * Translations sets are based on the ID of the node containing the source
   * text for the translation set.
   *
   * @var integer
   */
  public $tnid;

  /**
   * The node translation status.
   *
   * If the translation page needs to be updated the value is 1, otherwise 0.
   *
   * @var integer
   */
  public $translate;

  /**
   * The node revision creation timestamp.
   *
   * @var integer
   */
  public $revision_timestamp;

  /**
   * The node revision author's user ID.
   *
   * @var integer
   */
  public $revision_uid;

  /**
   * Implements Drupal\entity\EntityInterface::id().
   */
  public function id() {
    return $this->nid;
  }

  /**
   * Implements Drupal\entity\EntityInterface::bundle().
   */
  public function bundle() {
    return $this->type;
  }

  /**
   * Overrides Drupal\entity\Entity::createDuplicate().
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();
    $duplicate->vid = NULL;
    return $duplicate;
  }

  /**
   * Overrides Drupal\entity\Entity::getRevisionId().
   */
  public function getRevisionId() {
    return $this->vid;
  }
}
