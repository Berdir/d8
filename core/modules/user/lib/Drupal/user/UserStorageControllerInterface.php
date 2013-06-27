<?php

/**
 * @file
 * Contains \Drupal\user\UserStorageControllerInterface.
 */

namespace Drupal\user;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines a common interface for user entity controller classes.
 */
interface UserStorageControllerInterface extends EntityStorageControllerInterface {

  /**
   * Save the user's roles.
   *
   * @param \Drupal\Core\Entity\EntityInterface $user
   */
  public function saveRoles(EntityInterface $user);

  /**
   * Remove the roles of a user.
   *
   * @param array $uids
   */
  public function deleteUserRoles(array $uids);

  /**
   * Returns role IDs of the provided users.
   *
   * @param array $uids
   *   User ID's for which roles should be returned.
   *
   * @return array
   *   An array of role ids per user, keyed by the user id.
   */
  public function getUserRoles(array $uids);

}
