<?php

/**
 * @file
 * Definition of Drupal\locale\TranslationStream.
 */

namespace Drupal\locale;

use Drupal\Core\StreamWrapper\LocalStream;

/**
 * Defines a Drupal translations (translations://) stream wrapper class.
 *
 * Provides support for storing translation files.
 */
class TranslationsStream extends LocalStream {

  /**
   * Implements Drupal\Core\StreamWrapper\LocalStream::getDirectoryPath()
   */
  public function getDirectoryPath() {
    return variable_get('locale_translate_file_directory',
      conf_path() . '/files/translations');
  }

  /**
   * Implements Drupal\Core\StreamWrapper\StreamWrapperInterface::getExternalUrl().
   *
   * @return string
   *   Returns the HTML URI of a public file.
   */
  function getExternalUrl() {
    $path = str_replace('\\', '/', $this->getTarget());
    return $GLOBALS['base_url'] . '/' . self::getDirectoryPath() . '/' . drupal_encode_path($path);
  }
}
