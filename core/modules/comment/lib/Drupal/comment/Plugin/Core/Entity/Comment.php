<?php

/**
 * @file
 * Definition of Drupal\comment\Plugin\Core\Entity\Comment.
 */

namespace Drupal\comment\Plugin\Core\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines the comment entity class.
 *
 * @Plugin(
 *   id = "comment",
 *   label = @Translation("Comment"),
 *   module = "comment",
 *   controller_class = "Drupal\comment\CommentStorageController",
 *   render_controller_class = "Drupal\comment\CommentRenderController",
 *   form_controller_class = {
 *     "default" = "Drupal\comment\CommentFormController"
 *   },
 *   translation_controller_class = "Drupal\comment\CommentTranslationController",
 *   base_table = "comment",
 *   uri_callback = "comment_uri",
 *   fieldable = TRUE,
 *   static_cache = FALSE,
 *   entity_keys = {
 *     "id" = "cid",
 *     "bundle" = "node_type",
 *     "label" = "subject",
 *     "uuid" = "uuid"
 *   },
 *   view_modes = {
 *     "full" = {
 *       "label" = "Full comment",
 *       "custom_settings" = FALSE
 *     }
 *   }
 * )
 */
class Comment extends Entity implements ContentEntityInterface {

  /**
   * The comment ID.
   *
   * @var integer
   */
  public $cid;

  /**
   * The comment UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The entity ID to which this comment is attached.
   *
   * @var integer
   */
  public $entity_id;

  /**
   * The entity type to which this comment is attached.
   *
   * @var string
   */
  public $entity_type;

  /**
   * The field to which this comment is attached.
   *
   * @var string
   */
  public $field_name = 'comment';

  /**
   * The parent comment ID if this is a reply to a comment.
   *
   * @var integer
   */
  public $pid;

  /**
   * The ID of the node to which the comment is attached.
   */
  public $nid;

  /**
   * The comment language code.
   *
   * @var string
   */
  public $langcode = LANGUAGE_NOT_SPECIFIED;

  /**
   * The comment title.
   *
   * @var string
   */
  public $subject;


  /**
   * The comment author ID.
   *
   * @var integer
   */
  public $uid = 0;

  /**
   * The comment author's name.
   *
   * For anonymous authors, this is the value as typed in the comment form.
   *
   * @var string
   */
  public $name = '';

  /**
   * The comment author's e-mail address.
   *
   * For anonymous authors, this is the value as typed in the comment form.
   *
   * @var string
   */
  public $mail;

  /**
   * The comment author's home page address.
   *
   * For anonymous authors, this is the value as typed in the comment form.
   *
   * @var string
   */
  public $homepage;

  /**
   * The entity which this comment is attached.
   *
   * @var Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->cid;
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::bundle().
   */
  public function bundle() {
    return $this->field_name;
  }
}
