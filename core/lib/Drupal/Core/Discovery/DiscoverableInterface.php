<?php

/**
 * @file
 * Contains \Drupal\Core\Discovery\DiscoverableInterface.
 */

namespace Drupal\Core\Discovery;

/**
 */
interface DiscoverableInterface {

  /**
   * @return array
   */
  public function findAll();

}
