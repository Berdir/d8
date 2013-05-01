<?php

/**
 * @file
 * Contains \Drupal\contact\MessageStorageController.
 */

namespace Drupal\contact;

use Drupal\Core\Entity\DatabaseStorageControllerNG;

/**
 * Defines the controller class for the contact message entity.
 */
class MessageStorageController extends DatabaseStorageControllerNG {

  /**
   * {@inheritdoc}
   */
  public function baseFieldDefinitions() {
    $fields['category'] = array(
      'label' => t('Category'),
      'description' => t('The ID of the associated category.'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'contact_category'),
    );
    $fields['name'] = array(
      'label' => t("The sender's name"),
      'description' => t('The name of the person that is sending the contact message.'),
      'type' => 'string_field',
    );
    $fields['mail'] = array(
      'label' => t("The sender's e-mail"),
      'description' => t('The e-mail of the person that is sending the contact message.'),
      'type' => 'string_field',
    );
    $fields['subject'] = array(
      'label' => t("The message subject"),
      'description' => t('The subject of the contact message.'),
      'type' => 'string_field',
    );
    $fields['message'] = array(
      'label' => t("The message text"),
      'description' => t('The text of the contact message.'),
      'type' => 'string_field',
    );
    $fields['copy'] = array(
      'label' => t("Copy"),
      'description' => t('Whether to send a copy of the message to the sender.'),
      'type' => 'string_field',
    );
    $fields['recipient'] = array(
      'label' => t('Recipient ID'),
      'description' => t('The ID of the recipient user.'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'user'),
      'translatable' => TRUE,
    );
    return $fields;
  }
}
