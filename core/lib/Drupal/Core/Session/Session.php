<?php

/**
 * @file
 * Defines Drupal\Core\Session\Session.
 */

namespace Drupal\Core\Session;

use Symfony\Component\HttpFoundation\Session\Session as BaseSession;

/**
 * Overrides Symfony's Session object in order to implement Drupal specific
 * session features, such as lazy cookie sending and explicit session save
 * disabling.
 */
class Session extends BaseSession {

  /**
   * Enable session save, at commit time session will be saved by the session
   * handler and session token will be sent.
   */
  public function enableSave() {
    $this->storage->getSaveHandler()->setActive(TRUE);
  }

  /**
   * Disable session save, at commit time session save will be skiped and
   * session token will not be sent to client.
   *
   * This function allows the caller to temporarily disable writing of
   * session data, should the request end while performing potentially
   * dangerous operations, such as manipulating the global $user object.
   * See http://drupal.org/node/218104 for usage.
   */
  public function disableSave() {
    $this->storage->getSaveHandler()->setActive(FALSE);
  }

  /**
   * Is the session save enabled.
   *
   * @return bool
   */
  public function isSaveEnabled() {
    $this->storage->getSaveHandler()->isActive();
  }

  /**
   * Does this session is empty.
   *
   * @todo This is the most absurd implementation that could ever been written
   * but there is no clean solution because bags can not be directly accessed
   * via protected attributes, and they don't have either a count() or isEmpty()
   * method.
   *
   * @return bool
   *   TRUE if session is empty.
   */
  public function isEmpty() {
    $empty = true;

    // @todo: This code is incredebly ugly and this foreach needs to be removed
    // once all Drupal code is ported to use the session bag instead of direct
    // $_SESSION superglobal usage.
    foreach ($_SESSION as $key => $value) {
      // Ignore SF2 attributes
      if (0 === strpos($key, '_sf2')) {
        continue;
      }
      $empty = false;
      break;
    }

    return $empty && !count($this->getFlashBag()->all()) && !count($this->all());
  }

  /**
   * Overrides Symfony\Component\HttpFoundation\Session\Session::migrate().
   *
   * Prevents session regenerate if saving is disabled.
   */
  public function migrate($destroy = false, $lifetime = null) {
    if (!$this->isSaveEnabled()) {
      return;
    }
    return $this->storage->regenerate($destroy, $lifetime);
  }

}
