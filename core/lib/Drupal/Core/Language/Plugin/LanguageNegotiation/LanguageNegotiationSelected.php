<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationSelected.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from a selected language.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationSelected::METHOD_ID,
 *   weight = 12,
 *   name = @Translation("Selected language"),
 *   description = @Translation("Language based on a selected language."),
 *   config = "admin/config/regional/language/detection/selected"
 * )
 */
class LanguageNegotiationSelected extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-selected';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    // Replace the site's default langcode by its real value.
    $langcode = isset($this->config['selected_langcode']) ? $this->config['selected_langcode'] : FALSE;
    // Replace the site's default langcode by its real value.
    if ($langcode == 'site_default') {
     $langcode = language_default()->id;
    }
    return isset($languages[$langcode]) ? $langcode : $this->languageManager->getLanguageDefault()->id;
  }

}
