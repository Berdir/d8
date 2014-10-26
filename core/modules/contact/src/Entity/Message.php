<?php

/**
 * @file
 * Contains Drupal\contact\Entity\Message.
 */

namespace Drupal\contact\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\contact\MessageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the contact message entity.
 *
 * @ContentEntityType(
 *   id = "contact_message",
 *   label = @Translation("Contact message"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\ContentEntityNullStorage",
 *     "view_builder" = "Drupal\contact\MessageViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\contact\MessageForm"
 *     }
 *   },
 *   entity_keys = {
 *     "bundle" = "contact_form",
 *     "uuid" = "uuid"
 *   },
 *   bundle_entity_type = "contact_form",
 *   field_ui_base_route = "entity.contact_form.edit_form",
 * )
 */
class Message extends ContentEntityBase implements MessageInterface {

  /**
   * {@inheritdoc}
   */
  public function isPersonal() {
    return $this->bundle() == 'personal';
  }

  /**
   * {@inheritdoc}
   */
  public function getContactForm() {
    return $this->get('contact_form')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getSenderName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSenderName($sender_name) {
    $this->set('name', $sender_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getSenderMail() {
    return $this->get('mail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSenderMail($sender_mail) {
    $this->set('mail', $sender_mail);
  }

  /**
   * {@inheritdoc}
   */
  public function getSubject() {
    return $this->get('subject')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubject($subject) {
    $this->set('subject', $subject);
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
  public function setMessage($message) {
    $this->set('message', $message);
  }

  /**
   * {@inheritdoc}
   */
  public function copySender() {
    return (bool)$this->get('copy')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCopySender($inform) {
    $this->set('copy', (bool) $inform);
  }

  /**
   * {@inheritdoc}
   */
  public function getPersonalRecipient() {
    if ($this->isPersonal()) {
      return $this->get('recipient')->entity;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['contact_form'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Form ID')
      ->setDescription('The ID of the associated form.')
      ->setSetting('target_type', 'contact_form')
      ->setRequired(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel('UUID')
      ->setDescription('The message UUID.')
      ->setReadOnly(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel('Language code')
      ->setDescription('The comment language code.');

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel("The sender's name")
      ->setDescription('The name of the person that is sending the contact message.');

    $fields['mail'] = BaseFieldDefinition::create('email')
      ->setLabel("The sender's email")
      ->setDescription('The email of the person that is sending the contact message.');

    // The subject of the contact message.
    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel('Subject')
      ->setRequired(TRUE)
      ->setSetting('max_length', 100)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    // The text of the contact message.
    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel('Message')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 0,
        'settings' => array(
          'rows' => 12,
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => 0,
        'label' => 'above',
      ))
      ->setDisplayConfigurable('view', TRUE);

    $fields['copy'] = BaseFieldDefinition::create('boolean')
      ->setLabel('Copy')
      ->setDescription('Whether to send a copy of the message to the sender.');

    $fields['recipient'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Recipient ID')
      ->setDescription('The ID of the recipient user for personal contact messages.')
      ->setSetting('target_type', 'user');

    return $fields;
  }

}
