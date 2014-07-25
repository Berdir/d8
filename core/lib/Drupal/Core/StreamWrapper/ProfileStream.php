<?php

/**
 * @file
 * Contains \Drupal\Core\StreamWrapper\ProfileStream.
 */

namespace Drupal\Core\StreamWrapper;

/**
 * Defines a read-only Drupal stream wrapper base class for profiles.
 *
 * This class extends the complete stream wrapper implementation in LocalStream.
 * URIs such as "profile://standard" are expanded to a normal filesystem path
 * such as "profiles/standard" and then PHP filesystem functions are invoked.
 *
 * Specifying "profile://current" will return a stream for the currently
 * installed profile.
 */
class ProfileStream extends SystemStream {

  /**
   * {@inheritdoc}
   */
  public function getOwnerName($uri = NULL) {
    $name = parent::getOwnerName($uri);
    $current = drupal_get_profile();
    switch ($name) {
      case 'current':
      case $current:
        return $current;
      default:
        return !is_null(drupal_get_filename('profile', $name)) ? $name : FALSE;
    }
  }

  /**
   * Gets the profile's directory path.
   *
   * @param string $uri
   *   Optional URI.
   *
   * @return string
   *   String specifying the path.
   */
  public function getDirectoryPath($uri = NULL) {
    return drupal_get_path('profile', $this->getOwnerName($uri));
  }
}
