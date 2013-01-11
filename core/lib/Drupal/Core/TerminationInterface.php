<?php

/**
 * @file
 * Contains \Drupal\Core\TerminationInterface.
 */

namespace Drupal\Core;

/**
 * The interface for services needing explicit termination.
 */
interface TerminationInterface {

  /**
   * Performs termination operations.
   */
  public function terminate();
}
