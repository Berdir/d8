<?php

namespace Drupal\Context\Handler;

use \Drupal\Context\ContextInterface;
use \Drupal\Context\Handler;

/**
 * Raw path Context Handler implementation.
 */
class HandlerPathRaw extends HandlerAbstract {

  public function getValue(array $args = array(), ContextInterface $context = null) {
    $raw_path = '';

    $q = $context->getValue('http:query:q');
    if (!empty($q)) {
      // This is a request with a ?q=foo/bar query string. $_GET['q'] is
      // overwritten in drupal_path_initialize(), but request_path() is called
      // very early in the bootstrap process, so the original value is saved in
      // $path and returned in later calls.
      $raw_path = $q;
    }
    else {
      // This request is either a clean URL, or 'index.php', or nonsense.
      // Extract the path from REQUEST_URI.
      $request_uri = $context->getValue('http:request_uri');
      $request_path = strtok($request_uri, '?');
      $script_name = $context->getValue('http:script_name');
      $base_path_len = strlen(rtrim(dirname($script_name), '\/'));
      // Unescape and strip $base_path prefix, leaving q without a leading slash.
      $raw_path = substr(urldecode($request_path), $base_path_len + 1);
      // If the path equals the script filename, either because 'index.php' was
      // explicitly provided in the URL, or because the server added it to
      // $_SERVER['REQUEST_URI'] even when it wasn't provided in the URL (some
      // versions of Microsoft IIS do this), the front page should be served.
      $php_self = $context->getValue('http:php_self');
      if ($raw_path == basename($php_self)) {
        $raw_path = '';
      }
    }

    // Under certain conditions Apache's RewriteRule directive prepends the value
    // assigned to $_GET['q'] with a slash. Moreover we can always have a trailing
    // slash in place, hence we need to normalize $_GET['q'].
    $raw_path = trim($raw_path, '/');

    return $raw_path;
  }

}
