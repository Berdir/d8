<?php

/**
 * @file
 * Contains \Drupal\Core\StreamWrapper\ModuleStream.
 */

namespace Drupal\Core\StreamWrapper;

/**
 * Defines a read-only Drupal stream wrapper base class for modules.
 *
 * This class extends the complete stream wrapper implementation in LocalStream.
 * URIs such as "module://system" are expanded to a normal filesystem path
 * such as "modules/system" and then PHP filesystem functions are
 * invoked.
 */
class ModuleStream extends SystemStream {

  /**
   * Get the module name of the current URI.
   *
   * @param string $uri
   *   Optional URI.
   *
   * @return string
   *   The extension name.
   */
  public function getOwnerName($uri = NULL) {
    $name = parent::getOwnerName($uri);
    return \Drupal::moduleHandler()->moduleExists($name) ? $name : FALSE;
  }

  /**
   * Gets the module's directory path.
   *
   * @param string $uri
   *   Optional URI.
   *
   * @return string
   *   String specifying the path.
   */
  public function getDirectoryPath($uri = NULL) {
    return drupal_get_path('module', $this->getOwnerName($uri));
  }
}
