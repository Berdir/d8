<?php

/**
 * @file
 * Contains \Drupal\contact\Plugin\Core\Entity\MessageInterface.
 */

namespace Drupal\contact;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface defining a contant message entity
 */
interface MessageInterface extends EntityInterface {

  /**
   * Returns the category this contact message belongs to.
   *
   * @return \Drupal\contact\CategoryInterface
   *   The contact category entity.
   */
  public function getCategory();

  /**
   * Returns the name of the sender.
   *
   * @return string
   *   The name name of the message sender.
   */
  public function getSenderName();

  /**
   * Returns the e-mail address of the sender.
   *
   * @return string
   *   The e-mail address of the message sender.
   */
  public function getSenderMail();

  /**
   * Returns the message subject.
   *
   * @return string
   *   The message subject.
   */
  public function getSubject();

  /**
   * Returns the message body.
   *
   * @return string
   *   The message body.
   */
  public function getMessage();

  /**
   * Returns TRUE if a copy should be sent to the sender.
   *
   * @return bool
   *   TRUE if a copy should be sent, FALSE if not.
   */
  public function copy();

  /**
   * Return TRUE if this is the personal contact form.
   *
   * @return bool
   *   TRUE if the message bundle is personal.
   */
  public function isPersonal();

  /**
   * Returns the user this message is being sent to.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity of the recipent, NULL if this is not a personal message.
   */
  public function getPersonalRecipient();

}
