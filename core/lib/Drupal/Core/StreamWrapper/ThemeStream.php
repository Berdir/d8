<?php

/**
 * @file
 * Contains \Drupal\Core\StreamWrapper\ThemeStream.
 */

namespace Drupal\Core\StreamWrapper;

/**
 * Defines a read-only Drupal stream wrapper base class for themes.
 *
 * This class extends the complete stream wrapper implementation in LocalStream.
 * URIs such as "theme://bartik" are expanded to a normal filesystem path
 * such as "themes/bartik" and then PHP filesystem functions are invoked.
 *
 * Specifying "theme://current" returns a stream for the current theme,
 * "theme://default" returns a stream for the default theme, and "theme://admin"
 * returns a stream for the admin theme.
 */
class ThemeStream extends SystemStream {

  /**
   * {@inheritdoc}
   */
  public function getOwnerName($uri = NULL) {
    global $theme_key;
    $name = parent::getOwnerName($uri);
    switch ($name) {
      case 'current':
        return $theme_key;
      case 'default':
        return \Drupal::config('system.theme')->get('default');
      case 'admin':
        return \Drupal::config('system.theme')->get('admin');
      default:
        // Return name only for enabled and admin themes.
        return \Drupal::service('access_check.theme')->checkAccess($name) ? $name : FALSE;
    }
  }

  /**
   * Gets the theme's directory path.
   *
   * @param string $uri
   *   Optional URI.
   *
   * @return string
   *   String specifying the path.
   */
  public function getDirectoryPath($uri = NULL) {
    return drupal_get_path('theme', $this->getOwnerName($uri));
  }

}
