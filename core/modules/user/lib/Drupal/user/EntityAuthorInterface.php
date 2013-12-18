<?php

/**
 * @file
 * Contains \Drupal\user\EntityAuthorInterface.
 */

namespace Drupal\user;

/**
 * Defines a common interface for entities that have an author or owner.
 */
interface EntityAuthorInterface {

  /**
   * Returns the entity author's user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The author user entity.
   */
  public function getAuthor();

  /**
   * Sets the entity author's user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The author user entity.
   *
   * @return self
   *   The called entity.
   */
  public function setAuthor(UserInterface $account);

  /**
   * Returns the entity author's user ID.
   *
   * @return int
   *   The author user ID.
   */
  public function getAuthorId();

  /**
   * Sets the entity author's user ID.
   *
   * @param int $uid
   *   The author user id.
   *
   * @return self
   *   The called entity.
   */
  public function setAuthorId($uid);

}
