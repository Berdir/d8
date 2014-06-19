<?php

/**
 * @file
 * Contains \Drupal\Core\Routing\CachedUrlGeneratorInterface.
 */

namespace Drupal\Core\Routing;

/**
 * Defines an interface for generating a URL from a route or system path.
 *
 * Provides additional methods for generators that cache the URLs.
 */
interface CachedUrlGeneratorInterface extends UrlGeneratorInterface {

  /**
   * Clears the caches of the URL generator.
   */
  public function clearCache();

}
