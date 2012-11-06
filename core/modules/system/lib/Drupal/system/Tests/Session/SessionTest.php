<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Session\SessionTest.
 */

namespace Drupal\system\Tests\Session;

use Drupal\simpletest\WebTestBase;

class SessionTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('session_test');

  public static function getInfo() {
    return array(
      'name' => 'Session tests',
      'description' => 'Drupal session handling tests.',
      'group' => 'Session'
    );
  }

  /**
   * Tests for session save disabling and session regeneration.
   */
  function testSessionSaveRegenerate() {
    // Test session hardening code from SA-2008-044.
    $user = $this->drupalCreateUser(array('access content'));

    // Enable sessions.
    $this->sessionReset($user->uid);

    // Make sure the session cookie is set as HttpOnly.
    $this->drupalLogin($user);
    $this->assertTrue(preg_match('/HttpOnly/i', $this->drupalGetHeader('Set-Cookie', TRUE)), 'Session cookie is set as HttpOnly.');
    $this->drupalLogout();

    // Verify that the session is regenerated if a module calls exit
    // in hook_user_login().
    $user->name = 'session_test_user';
    $user->save();
    $this->drupalGet('session-test/id');
    $matches = array();
    preg_match('/\s*session_id:(.*)\n/', $this->drupalGetContent(), $matches);
    $this->assertTrue(!empty($matches[1]) , 'Found session ID before logging in.');
    $original_session = $matches[1];

    // We cannot use $this->drupalLogin($user); because we exit in
    // session_test_user_login() which breaks a normal assertion.
    $edit = array(
      'name' => $user->name,
      'pass' => $user->pass_raw
    );
    $this->drupalPost('user', $edit, t('Log in'));
    $this->drupalGet('user');
    $pass = $this->assertText($user->name, format_string('Found name: %name', array('%name' => $user->name)), t('User login'));
    $this->_logged_in = $pass;

    $this->drupalGet('session-test/id');
    $matches = array();
    preg_match('/\s*session_id:(.*)\n/', $this->drupalGetContent(), $matches);
    $this->assertTrue(!empty($matches[1]) , 'Found session ID after logging in.');
    $this->assertTrue($matches[1] != $original_session, 'Session ID changed after login.');
  }

  /**
   * Test data persistence via the session_test module callbacks. Also tests
   * drupal_session_count() since session data is already generated here.
   */
  function testDataPersistence() {
    $user = $this->drupalCreateUser(array('access content'));
    // Enable sessions.
    $this->sessionReset($user->uid);

    $this->drupalLogin($user);

    $value_1 = $this->randomName();
    $this->drupalGet('session-test/set/' . $value_1);
    $this->assertText($value_1, 'The session value was stored.', t('Session'));
    $this->drupalGet('session-test/get');
    $this->assertText($value_1, 'Session correctly returned the stored data for an authenticated user.', t('Session'));

    // Attempt to write over val_1. while session save is disabled.
    // properly, val_1 will still be set.
    $value_2 = $this->randomName();
    $this->drupalGet('session-test/no-set/' . $value_2);
    $this->assertText($value_2, 'The session value was correctly passed to session-test/no-set.', t('Session'));
    $this->drupalGet('session-test/get');
    $this->assertText($value_1, 'Session data is not saved when session save is disabled.', t('Session'));
    $this->assertNoText($value_2, 'Session data is not saved when session save is disabled.', t('Session'));

    // Switch browser cookie to anonymous user, then back to user 1.
    $this->sessionReset();
    $this->sessionReset($user->uid);
    $this->assertText($value_1, 'Session data persists through browser close.', t('Session'));

    // Logout the user and make sure the stored value no longer persists.
    $this->drupalLogout();
    $this->sessionReset();
    $this->drupalGet('session-test/get');
    $this->assertNoText($value_1, "After logout, previous user's session data is not available.", t('Session'));

    // Now try to store some data as an anonymous user.
    $value_3 = $this->randomName();
    $this->drupalGet('session-test/set/' . $value_3);
    $this->assertText($value_3, 'Session data stored for anonymous user.', t('Session'));
    $this->drupalGet('session-test/get');
    $this->assertText($value_3, 'Session correctly returned the stored data for an anonymous user.', t('Session'));

    // Try to store data when session save is disabled.
    $value_4 = $this->randomName();
    $this->drupalGet('session-test/no-set/' . $value_4);
    $this->assertText($value_4, 'The session value was correctly passed to session-test/no-set.', t('Session'));
    $this->drupalGet('session-test/get');
    $this->assertText($value_3, 'Session data is not saved when session save is disabled.', t('Session'));

    // Login, the data should persist.
    $this->drupalLogin($user);
    $this->sessionReset($user->uid);
    $this->drupalGet('session-test/get');
    $this->assertNoText($value_1, 'Session has persisted for an authenticated user after logging out and then back in.', t('Session'));

    // Change session and create another user.
    $user2 = $this->drupalCreateUser(array('access content'));
    $this->sessionReset($user2->uid);
    $this->drupalLogin($user2);
  }

  /**
   * Test that empty anonymous sessions are destroyed.
   */
  function testEmptyAnonymousSession() {
    // Verify that no session is automatically created for anonymous user.
    $this->drupalGet('');
    $this->assertSessionCookie(FALSE);
    $this->assertSessionEmpty(TRUE);

    // The same behavior is expected when caching is enabled.
    $config = config('system.performance');
    $config->set('cache.page.enabled', 1);
    $config->save();
    $this->drupalGet('');
    $this->assertSessionCookie(FALSE);
    $this->assertSessionEmpty(TRUE);
    $this->assertEqual($this->drupalGetHeader('X-Drupal-Cache'), 'MISS', 'Page was not cached.');

    // Start a new session by setting a message.
    $this->drupalGet('session-test/set-message');
    $this->assertSessionCookie(TRUE);
    $this->assertTrue($this->drupalGetHeader('Set-Cookie'), 'New session was started.');

    // Display the message, during the same request the session is destroyed
    // and the session cookie is unset.
    $this->drupalGet('');
    $this->assertSessionCookie(FALSE);
    $this->assertSessionEmpty(FALSE);
    $this->assertFalse($this->drupalGetHeader('X-Drupal-Cache'), 'Caching was bypassed.');
    $this->assertText(t('This is a dummy message.'), 'Message was displayed.');
    $this->assertTrue(preg_match('/SESS\w+=deleted/', $this->drupalGetHeader('Set-Cookie')), 'Session cookie was deleted.');

    // Verify that session was destroyed.
    $this->drupalGet('');
    $this->assertSessionCookie(FALSE);
    $this->assertSessionEmpty(TRUE);
    $this->assertNoText(t('This is a dummy message.'), 'Message was not cached.');
    $this->assertEqual($this->drupalGetHeader('X-Drupal-Cache'), 'HIT', 'Page was cached.');
    $this->assertFalse($this->drupalGetHeader('Set-Cookie'), 'New session was not started.');

    // Verify that no session is created if session save is disabled.
    $this->drupalGet('session-test/set-message-but-dont-save');
    $this->assertSessionCookie(FALSE);
    $this->assertSessionEmpty(TRUE);

    // Verify that no message is displayed.
    $this->drupalGet('');
    $this->assertSessionCookie(FALSE);
    $this->assertSessionEmpty(TRUE);
    $this->assertNoText(t('This is a dummy message.'), 'The message was not saved.');
  }

  /**
   * Test that empty session IDs are not allowed.
   *
   * Is that test really necessary? An empty session id would be synonym of a
   * underlaying bug and not treated as a possible edge case.
   *
  function testEmptySessionID() {
    $user = $this->drupalCreateUser(array('access content'));
    $this->drupalLogin($user);
    $this->drupalGet('session-test/is-logged-in');
    $this->assertResponse(200, 'User is logged in.');

    // Reset the sid in {sessions} to a blank string. This may exist in the
    // wild in some cases, although we normally prevent it from happening.
    db_query("UPDATE {sessions} SET sid = '' WHERE uid = :uid", array(':uid' => $user->uid));
    // Send a blank sid in the session cookie, and the session should no longer
    // be valid. Closing the curl handler will stop the previous session ID
    // from persisting.
    $this->curlClose();
    $this->additionalCurlOptions[CURLOPT_COOKIE] = rawurlencode($this->session_name) . '=;';
    $this->drupalGet('session-test/id-from-cookie');
    $this->assertRaw("session_id:\n", 'Session ID is blank as sent from cookie header.');
    // Assert that we have an anonymous session now.
    $this->drupalGet('session-test/is-logged-in');
    $this->assertResponse(403, 'An empty session ID is not allowed.');
  }
   */

  /**
   * Reset the cookie file so that it refers to the specified user.
   *
   * @param $uid User id to set as the active session.
   */
  function sessionReset($uid = 0) {
    // Close the internal browser.
    $this->curlClose();
    $this->loggedInUser = FALSE;

    // Change cookie file for user.
    $this->cookieFile = file_stream_wrapper_get_instance_by_scheme('temporary')->getDirectoryPath() . '/cookie.' . $uid . '.txt';
    $this->additionalCurlOptions[CURLOPT_COOKIEFILE] = $this->cookieFile;
    $this->additionalCurlOptions[CURLOPT_COOKIESESSION] = TRUE;
    $this->drupalGet('session-test/get');
    $this->assertResponse(200, 'Session test module is correctly enabled.', t('Session'));
  }

  /**
   * Assert whether the SimpleTest browser sent a session cookie.
   */
  function assertSessionCookie($sent) {
    if ($sent) {
      $this->assertNotNull($this->session_id, 'Session cookie was sent.');
    }
    else {
      $this->assertNull($this->session_id, 'Session cookie was not sent.');
    }
  }

  /**
   * Assert whether $_SESSION is empty at the beginning of the request.
   */
  function assertSessionEmpty($empty) {
    if ($empty) {
      $this->assertIdentical($this->drupalGetHeader('X-Session-Empty'), '1', 'Session was empty.');
    }
    else {
      $this->assertIdentical($this->drupalGetHeader('X-Session-Empty'), '0', 'Session was not empty.');
    }
  }
}
