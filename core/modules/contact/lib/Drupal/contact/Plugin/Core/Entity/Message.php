<?php

/**
 * @file
 * Contains Drupal\contact\Plugin\Core\Entity\Message.
 */

namespace Drupal\contact\Plugin\Core\Entity;

use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityNG;
use Drupal\contact\MessageInterface;

/**
 * Defines the contact message entity.
 *
 * @EntityType(
 *   id = "contact_message",
 *   label = @Translation("Contact message"),
 *   module = "contact",
 *   controllers = {
 *     "storage" = "Drupal\contact\MessageStorageController",
 *     "render" = "Drupal\contact\MessageRenderController",
 *     "form" = {
 *       "default" = "Drupal\contact\MessageFormController"
 *     }
 *   },
 *   entity_keys = {
 *     "bundle" = "category"
 *   },
 *   fieldable = TRUE,
 *   bundle_keys = {
 *     "bundle" = "id"
 *   }
 * )
 */
class Message extends EntityNG implements MessageInterface {

  /**
   * Overrides Drupal\Core\Entity\Entity::id().
   */
  public function id() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isPersonal() {
    return $this->bundle() == 'personal';
  }

  /**
   * {@inheritdoc}
   */
  public function getCategory() {
    return $this->get('category')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getSenderName() {
    $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSenderMail() {
    $this->get('mail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubject() {
    $this->get('subject')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    return $this->get('message')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function copy() {
    return (bool)$this->get('copy')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPersonalRecipient() {
    if ($this->isPersonal()) {
      return $this->get('recipient')->entity;
    }
  }

}
