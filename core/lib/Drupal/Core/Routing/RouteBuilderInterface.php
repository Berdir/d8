<?php

/**
 * @file
 * Definition of Drupal\Core\Routing\RouteBuilderInterface.
 */

namespace Drupal\Core\Routing;

interface RouteBuilderInterface {

  const REBUILD_NEEDED = 'router_rebuild_needed';

  /**
   * Rebuilds the route info and dumps to dumper.
   *
   * @return bool
   *   Returns TRUE if the rebuild succeeds, FALSE otherwise.
   */
  public function rebuild();

  /**
   * Rebuilds the route info and dumps to dumper if necessary.
   *
   * @return bool
   *   Returns TRUE if the rebuild occurs, FALSE otherwise.
   */
  public function rebuildIfNeeded();

  /**
   * Set the router to be rebuilt next next it is needed.
   */
  public function setRebuildNeeded();

}
