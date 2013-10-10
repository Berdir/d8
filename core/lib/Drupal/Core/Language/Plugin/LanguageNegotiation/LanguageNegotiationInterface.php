<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationInterface.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language based on the interface language.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationInterface::METHOD_ID,
 *   types = {Drupal\Core\Language\Language::TYPE_CONTENT},
 *   weight = 8,
 *   name = @Translation("Interface"),
 *   description = @Translation("Use the detected interface language.")
 * )
 */
class LanguageNegotiationInterface extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-interface';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    return $this->languageManager->getLanguage();
  }

}
