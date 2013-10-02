<?php

/**
 * @file
 * Contains \Drupal\Core\Language\LanguageNegotiationInterface.
 */

namespace Drupal\Core\Language;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for language negotiation classes.
 */
interface LanguageNegotiationInterface {

  /**
   * Sets the language manager.
   *
   * @param LanguageManager $languageManager
   *   The language manager to be used to retrieve the language list and the
   *   already negotiated languages.
   */
  public function setLanguageManager(LanguageManager $languageManager);

  /**
   * Performs language negotiation.
   *
   * @param array $languages
   *   An array of valid languages.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   (optional) The current request. Defaults to NULL if it has not been
   *   initialized yet.
   */
  public function negotiateLanguage(array $languages, Request $request = NULL);

}
