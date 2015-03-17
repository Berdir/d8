<?php

/**
 * @file
 * Contains \Drupal\Core\PageCache\DefaultResponsePolicy.
 */

namespace Drupal\Core\PageCache;

/**
 * The default page cache response policy.
 *
 * Ensures server error responses are not cached.
 */
class DefaultResponsePolicy extends ChainResponsePolicy {

  /**
   * Constructs the default page cache response policy.
   */
  public function __construct() {
    $this->addPolicy(new ResponsePolicy\NoServerError());
  }

}

