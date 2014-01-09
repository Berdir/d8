<?php

/**
 * @file
 * Contains \Drupal\Core\Routing\RouteBuilderStatic.
 */

namespace Drupal\Core\Routing;

/**
 * This builds a static version of the router.
 */
class RouteBuilderStatic implements RouteBuilderInterface {

  /**
   * @inheritdoc
   */
  public function rebuild() {
    // @todo Add the route for the batch pages when that conversion happens,
    //   http://drupal.org/node/1987816.
  }

  /**
   * @inheritdoc
   */
  public function rebuildIfNeeded(){
    // @todo Add the route for the batch pages when that conversion happens,
    //   http://drupal.org/node/1987816.
  }

  /**
   * @inheritdoc
   */
  public function setRebuildNeeded() {
    // @todo Add the route for the batch pages when that conversion happens,
    //   http://drupal.org/node/1987816.

  }

}
