<?php

/**
 * @file
 * Contains \Drupal\Core\SmartCache\DefaultRequestPolicy.
 */

namespace Drupal\Core\SmartCache;

use Drupal\Core\PageCache\ChainRequestPolicy;
use Drupal\Core\PageCache\RequestPolicy\CommandLineOrUnsafeMethod;
use Drupal\Core\PageCache\RequestPolicy\NoAdminRoutes;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * The default SmartCache request policy.
 *
 * Delivery of cached pages is denied if either the application is running from
 * the command line or the request was not initiated with a safe method (GET or
 * HEAD).
 */
class DefaultRequestPolicy extends ChainRequestPolicy {

  /**
   * Constructs the default SmartCache request policy.
   */
  public function __construct() {
    $this->addPolicy(new CommandLineOrUnsafeMethod());
  }

}
