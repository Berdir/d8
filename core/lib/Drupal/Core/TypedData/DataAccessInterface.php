<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataAccessInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for
 */
interface DataAccessInterface {

  /**
   * Check data value access.
   *
   * @param \Drupal\user\User $account
   *   (optional) The user account to check access for. Defaults to the current
   *   user.
   *
   * @return bool
   *   Whether the given user has access.
   */
  public function access(\Drupal\user\User $account = NULL);

}
