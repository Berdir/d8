<?php

/**
 * @file
 * User session handling functions.
 *
 * This file is the first Symfony session usage test.
 */

/**
 * Initializes the session handler, starting a session if needed.
 *
 * @todo Move this into a lazy user loading once Drupal will got a fully
 * featured component registry (aKa DIC).
 */
function drupal_session_initialize() {
  // Symfony does not want to do it by itself, so we need to manually load
  // the SessionHandlerInterface file if PHP core is prior to 5.4.0
  // This is handled by the autoloader in Symfony, I don't know if this would
  // be a good idea for us to do the same.
  if (version_compare(phpversion(), '5.4.0', '<')) {
    require_once DRUPAL_ROOT . '/core/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Resources/stubs/SessionHandlerInterface.php';
  }

  // Force user global to exists.
  // @todo Set a user provider component somewhere in DIC instead.
  _drupal_session_load_user();
}

/**
 * Load user using the uid the session actually holds.
 *
 * FIXME: Ideally this would be exported into the user module or any other
 * system and the user would be lazy loaded on first access attempt, thus
 * allowing real session lazy load for pages that don't do any user access
 * checks.
 *
 * @todo: Replace this with a container lazy initialization function instead.
 *
 * @return object
 *   User account
 *
 * @see drupal_session_initialize()
 * @deprecated
 */
function _drupal_session_load_user() {
  global $user;

  $session = drupal_container()->get('session');

  // The Session::has() call will be the first session attribute get in the
  // page workflow, as long as this is early called during bootstrap, and
  // will trigger the session start.
  if ($session->has('uid') && ($uid = $session->get('uid'))) {

    $user = db_select('users', 'u')
      ->fields('u')
      ->condition('u.uid', $session->get('uid'))
      ->execute()
      ->fetch();

    if ($user && $user->uid > 0 && $user->status == 1) {
      // We found the client's session record and there is an authenticated
      // active user.
      $user->data = unserialize($user->data);
      $user->roles = array();
      $user->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';
      $user->roles += db_query("SELECT r.rid, r.name FROM {role} r INNER JOIN {users_roles} ur ON ur.rid = r.rid WHERE ur.uid = :uid", array(':uid' => $user->uid))->fetchAllKeyed(0, 1);
    }
    elseif ($user) {
      // The user is anonymous or blocked.
      $user = drupal_anonymous_user();
    }
    else {
      // User does not exists anymore or session data has expired.
      $user = drupal_anonymous_user();
    }
  }
  // During install session must carry a user name in order for the session
  // commit algorithm to send the cookie headers due to the lazy cookie
  // sending algorithm.
  else if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === 'install') {
    $user = (object)array(
      'uid' => 1,
      'hostname' => ip_address(),
      // 1 is DRUPAL_AUTHENTICATED_RID we hitting this during install and may
      // not have the constant yet.
      'roles' => array(1 => 1),
      // This will avoid some wrong PHP warnings due to incomplete user loading
      'timezone' => @date_default_timezone_get(),
    );
    $session->set('uid', 1);
  }
  else {
    // No session uid is set, meaning the session does not exists or the user
    // is anonymous.
    $user = drupal_anonymous_user();
  }

  // Core can cache pages if session is empty (no flash messages) and user
  // is not logged in.
  if (!empty($user->uid) || !$session->isEmpty()) {
    drupal_page_is_cacheable(FALSE);
  }

  date_default_timezone_set(drupal_get_user_timezone());
}

/**
 * Commits the current session, if necessary.
 * @todo: This should move into an AbstractProxy implementation instead.
 */
function drupal_session_commit() {
  global $user;

  $session = drupal_container()->get('session');

  if (!$session->isSaveEnabled()) {
    // In case business layer specifically asked for not saving the session, we
    // need to unregister potential handlers the Symfony session storage
    // component may have registered for us. Considering that this function is
    // only run when Drupal is doing its proper shutdown, we can safely assume
    // the session has not been automatically saved by PHP at shutdown.
    // Notice that this check is duplicated into the Session::save() method in
    // order to avoid accidental save. This check here only exists for minor
    // performance reasons.
    return;
  }

  if (empty($user->uid)) {
    // Ensure there is no 'uid' set in session. Keeping an outdated or empty
    // session 'uid' attributes would taint the Session::isEmpty() check and
    // give potential false positives, thus forcing empty session to be saved.
    $session->remove('uid');
  }
  else {
    // Ensures user 'uid' which may not be set yet.
    $session->set('uid', $user->uid);
  }

  if ($session->isEmpty()) {
    // Force any empty session to be destroyed, this will avoid next bootstrap
    // with the same client to attempt a useless user initialization and session
    // read thus saving precious SQL queries.
    $session->invalidate();
  }
  else {
    // Save the session only if necessary.
    $session->save();
  }
}
