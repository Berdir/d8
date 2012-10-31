<?php

/**
 * @file
 * Defines Drupal\Core\Session\Proxy\CookieOverrideProxy.
 */

namespace Drupal\Core\Session\Proxy;

use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

// @todo Replace this at the correct place.
// If a session cookie exists, initialize the session. Otherwise the
// session is only started on demand in drupal_session_commit(), making
// anonymous users not use a session cookie unless something is stored in
// $_SESSION. This allows HTTP proxies to cache anonymous pageviews.

/**
 * Custom SessionHandlerProxy implementation that allows us to handle the HTTP
 * and HTTPS session cookies manually, and enfore strong security measures for
 * the session handling.
 */
class CookieOverrideProxy extends SessionHandlerProxy {

  /**
   * Defautl Constructor.
   *
   * @param \SessionHandlerInterface $handler
   */
  public function __construct(\SessionHandlerInterface $handler) {
    parent::__construct($handler);

    if ($id = $this->getIdFromCookie()) {
      $this->setId($id);
    }
    else {
      // Set a session identifier for this request. This is necessary because we
      // lazily start sessions at the end of this request, and some processes
      // (like drupal_get_token()) needs to know the future session ID i
      // advance.
      $GLOBALS['lazy_session'] = TRUE;

      // Less random sessions (which are much faster to generate) are used for
      // anonymous users than are generated in drupal_session_regenerate() when
      // a user becomes authenticated.
      $this->regenerateId();

      /*
       * @todo Restore HTTPS cookie
      if ($is_https && variable_get('https', FALSE)) {
        $insecure_session_name = substr(session_name(), 1);
        $session_id = drupal_hash_base64(uniqid(mt_rand(), TRUE));
        $_COOKIE[$insecure_session_name] = $session_id;
      }
       */
    }
  }

  /**
   * Get current session identifier from cookie, if any.
   *
   * @return string
   *   Session identifier or NULL if none found.
   */
  protected function getIdFromCookie() {
    $name = $this->getName();
    if (!empty($_COOKIE[$name])) {
      // @todo Restore HTTPS cookie
      //|| ($GLOBALS['is_https'] && variable_get('https', FALSE) && !empty($_COOKIE[substr(session_name(), 1)]))) {
      return $_COOKIE[$name];
    }
  }

  protected function destroyCookies() {
    if (headers_sent()) {
      //throw new \RuntimeException('Failed to destroy cookies because headers have already been sent.');
    }

    $params = session_get_cookie_params();
    // @todo Restore HTTPS cookie
    setcookie($this->getName(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }

  protected function sendCookies($id) {
    if (headers_sent()) {
      //throw new \RuntimeException('Failed to set cookies because headers have already been sent.');
    }

    $params = session_get_cookie_params();
    $expire = $params['lifetime'] ? time() + $params['lifetime'] : 0;
    // @todo Restore HTTPS cookie
    setcookie($this->getName(), $id, $expire, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }

  public function write($id, $data) {

    if (!$this->active) {
      return FALSE;
    }

    // Cookie sending must be when we are sure we need to keep the session, this
    // ensure the lazy session init. Lazy session init is abusive talking we are
    // not lazy initializing the session, but lazy sending the session cookie
    // instead. Each anonymous user will intrinsecly have a session tied, which
    // allows to generate tokens for forms and such, but if the session ends up
    // empty, the cookies will not be sent and the session will not be saved on
    // disk.
    $this->sendCookies($id);

    return (bool) $this->handler->write($id, $data);
  }

  public function destroy($id) {
    $this->destroyCookies($id);

    return (bool) $this->handler->destroy($id);
  }

  /**
   * Generate new session identifier.
   *
   * The the session_regenerate_id() is hardcoded into Symfony's
   * NativeSessionStorage implementation while all other session_*() functions
   * are used as setters only in the AbstractProxy implementation. This feels
   * wrong and we need to override it without doing invasive changes.
   *
   * @todo Propose a nice PR to Symfony guys so they move this specific call
   * into the SessionHandlerProxy so we wouldn't have to override the
   * NativeSessionStorage at all.
   *
   * @see Drupal\Core\Session\Proxy\Storage\DrupalSessionStorage::regenerate()
   *
   * @param bool $destroy
   *   (optional) If set to TRUE, destroy the old session.
   *
   * @return string
   *   New session identifier.
   */
  public function regenerateId($destroy = FALSE) {
    $id = drupal_hash_base64(uniqid(mt_rand(), TRUE) . drupal_random_bytes(55));

    // Do not call parent::setId() here, else it will throw exceptions because
    // during session identifier regeneration, this component is considered as
    // active.
    session_id($id);

    if ($destroy) {
      $this->destroyCookies();
    }

    return TRUE;
  }
}
