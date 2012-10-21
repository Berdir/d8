<?php

/**
 * @file
 * Definition of Drupal\comment\Comment.
 */

namespace Drupal\comment;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityNG;

/**
 * Defines the comment entity class.
 */
class Comment extends EntityNG implements ContentEntityInterface {

  /**
   * The comment ID.
   *
   * @todo: Rename to 'id'.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $cid;

  /**
   * The comment UUID.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $uuid;

  /**
   * The parent comment ID if this is a reply to a comment.
   *
   * @todo: Rename to 'parent_id'.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $pid;

  /**
   * The ID of the node to which the comment is attached.
   */
  public $nid;

  /**
   * The comment language code.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $langcode;

  /**
   * The comment title.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $subject;


  /**
   * The comment author ID.
   *
   * @todo: Rename to 'user_id'.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $uid = 0;

  /**
   * The comment author's name.
   *
   * For anonymous authors, this is the value as typed in the comment form.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $name;

  /**
   * The comment author's e-mail address.
   *
   * For anonymous authors, this is the value as typed in the comment form.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $mail;

  /**
   * The comment author's home page address.
   *
   * For anonymous authors, this is the value as typed in the comment form.
   *
   * @var \Drupal\Core\Entity\Property\ItemListInterface
   */
  public $homepage;

  /**
   * The plain data values of the contained properties.
   *
   * Define some default values used.
   *
   * @var array
   */
  protected $values = array(
    'langcode' => array(LANGUAGE_DEFAULT => array(0 => array('value' => LANGUAGE_NOT_SPECIFIED))),
    'name' => array(LANGUAGE_DEFAULT => array(0 => array('value' => ''))),
    'uid' => array(LANGUAGE_DEFAULT => array(0 => array('value' => 0))),
  );

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    // We unset all defined properties, so magic getters apply.
    unset($this->cid);
    unset($this->langcode);
    unset($this->uuid);
    unset($this->pid);
    unset($this->subject);
    unset($this->uid);
    unset($this->name);
    unset($this->mail);
    unset($this->homepage);
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('cid')->value;
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::bundle().
   */
  public function bundle() {
    return $this->get('node_type')->value;
  }
}
