<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\AccessibleInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for checking access.
 */
interface AccessibleInterface {

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
