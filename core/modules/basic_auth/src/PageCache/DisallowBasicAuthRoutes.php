<?php

/**
 * @file
 * Contains \Drupal\basic_auth\PageCache\DisallowBasicAuthRoutes.
 */

namespace Drupal\basic_auth\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cache policy for pages served from basic auth.
 *
 * The page cache must be disabled for any request using basic auth credentials
 * because the page cache system does not take the HTTP Basic Auth headers into
 * account. Otherwise this can result in access bypass security issues when an
 * authenticated user response gets into the page cache.
 */
class DisallowBasicAuthRoutes implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    $username = $request->headers->get('PHP_AUTH_USER');
    $password = $request->headers->get('PHP_AUTH_PW');
    if (isset($username) && isset($password)) {
      return self::DENY;
    }
  }

}
