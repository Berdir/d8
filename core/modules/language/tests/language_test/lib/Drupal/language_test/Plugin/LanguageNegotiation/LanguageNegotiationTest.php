<?php

/**
 * @file
 * Contains \Drupal\language_test\\Plugin\LanguageNegotiation\LanguageNegotiationTest.
 */

namespace Drupal\language_test\Plugin\LanguageNegotiation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from a selected language.
 *
 * @Plugin(
 *   id = LanguageNegotiationTest::METHOD_ID,
 *   weight = -10,
 *   name = @Translation("Test"),
 *   description = @Translation("This is a test language negotiation method."),
 *   types = {Language::TYPE_CONTENT, "test_language_type", "fixed_test_language_type"}
 * )
 */
class LanguageNegotiationTest extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'test_language_negotiation_method';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    return 'it';
  }

}
