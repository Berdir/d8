<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUI.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying the language from the current interface language.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUI::METHOD_ID,
 *   types = {Drupal\Core\Language\Language::TYPE_CONTENT},
 *   weight = 9,
 *   name = @Translation("Interface"),
 *   description = @Translation("Use the detected interface language.")
 * )
 */
class LanguageNegotiationUI extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-interface';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    return $this->languageManager->getLanguage()->id;
  }

}
