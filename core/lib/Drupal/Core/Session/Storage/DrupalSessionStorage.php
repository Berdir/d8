<?php

/**
 * @file
 * Defines Drupal\Core\Session\Storage\DrupalSessionStorage.
 */

namespace Drupal\Core\Session\Storage;

use Drupal\Core\Session\Proxy\CookieOverrideProxy;

use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Default session storage.
 *
 * This is a proxy class between the $_SESSION super global and the Session
 * object bags. There is no way on earth we would want to write our own
 * implementation, this one only exists in order to override some harcoded PHP
 * ini by the Symfony implementation that may disturb some Drupal cookie
 * handling implementation details.
 *
 * In opposition to Symfony 2.0, this proxy implementation will allow us to use
 * the $_SESSION array without worrying about loosing data, all we need to do is
 * to check that our own code hits $_SESSION keys that are synchronized to this
 * object's bags.
 */
class DrupalSessionStorage extends NativeSessionStorage {

  public function __construct(array $options = array(), $handler = null, MetadataBag $metaBag = null) {
    // Set PHP defaults to fit with our session usage.
    ini_set('session.auto_start', 0);
    ini_set('session.use_cookies', 0);

    // In the parent class, the session_register_shutdown() is called. Because
    // PHP native session will run the close handler in the PHP shutdown hooks,
    // most Drupal systems our handler relies on will be destructed before this
    // call. This is the main reason why we need to extends Symfony's component
    // in order to avoid the native shutdown to run.
    $this->setMetadataBag($metaBag);
    $this->setOptions($options);
    $this->setSaveHandler($handler);
  }

  public function clear() {
    parent::clear();

    // Clearing the session is a signal sent when session is invalidated, this
    // means we can mark the session handler as inactive so it won't attempt
    // any empty session write. Our session handler will send session cookie at
    // write time. This allows lazy cookie sending to the client.
    $this->saveHandler->setActive(FALSE);
  }

  public function regenerate($destroy = FALSE, $lifetime = NULL) {

    if (null !== $lifetime) {
      ini_set('session.cookie_lifetime', $lifetime);
    }

    if ($destroy) {
      $this->metadataBag->stampNew();
    }

    // If the current save handler is our own we must rely its own session
    // identifier generation method. I hope Symfony will move this call to
    // this object so we can get rid of this method override.
    if ($this->saveHandler instanceof CookieOverrideProxy) {
      return $this->saveHandler->regenerateId($destroy);
    }
    else {
      return session_regenerate_id($destroy);
    }
  }
}
