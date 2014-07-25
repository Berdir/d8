<?php

/**
 * @file
 * Contains \Drupal\Core\StreamWrapper\SystemStream.
 */

namespace Drupal\Core\StreamWrapper;

use \Drupal\Component\Utility\UrlHelper;

/**
 * Defines a base stream wrapper implementation.
 *
 * This class provides a read-only Drupal stream wrapper base class for system
 * files such as modules, themes and profiles.
 */
abstract class SystemStream extends LocalReadOnlyStream {

  /**
   * Get the module, theme, or profile name of the current URI.
   *
   * @param string $uri
   *   Optional URI.
   *
   * @return string
   *   The extension name.
   */
  public function getOwnerName($uri = NULL) {
    if (!isset($uri)) {
      $uri = $this->uri;
    }

    $uri_parts = explode('://', $uri, 2);
    if (count($uri_parts) === 1) {
      // The delimiter ('://') was not found in $uri, malformed $uri passed.
      throw new \InvalidArgumentException('Malformed $uri parameter passed: %s', $uri);
    }
    else {
      list($scheme, $target) = $uri_parts;
    }
    // Remove the trailing filename from the path.
    $length = strpos($target, '/');
    return ($length === FALSE) ? $target : substr($target, 0, $length);
  }

  /**
   * {@inheritdoc}
   */
  public function getTarget($uri = NULL) {
    $target = $this->extractTarget($uri);
    return file_exists($this->getDirectoryPath($uri) . '/' . $target) ? $target : NULL;
  }

  /**
   * Returns the local target of the resource, regardless of whether it exists.
   *
   * @param string $uri
   *   Optional URI.
   *
   * @return bool|string
   *   A path to the local target.
   */
  protected function extractTarget($uri = NULL) {
    // If the owner doesn't exist at all, we don't extract anything.
    if ($this->getOwnerName($uri) === FALSE) {
      return FALSE;
    }
    $target = parent::getTarget($uri);
    // Remove the preceding owner name including slash from the path.
    $start = strpos($target, '/');
    $target = ($start === FALSE) ? '' : substr($target, $start + 1);
    return $target;
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri = NULL) {
    $dir = $this->getDirectoryPath($uri);
    if (empty($dir)) {
      return FALSE;
    }

    $target = $this->extractTarget($uri);
    $path = $target != '' ? '/' . UrlHelper::encodePath(str_replace('\\', '/', $target)) : '';
    return \Drupal::request()->getBaseUrl() . '/' . $dir . $path;
  }
}
