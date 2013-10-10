<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrlFallback.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class that determines the language to be assigned to URLs when none is
 * detected.
 *
 * The language negotiation process has a fallback chain that ends with the
 * default language negotiation method. Each built-in language type has a
 * separate initialization:
 * - Interface language, which is the only configurable one, always gets a valid
 *   value. If no request-specific language is detected, the default language
 *   will be used.
 * - Content language merely inherits the interface language by default.
 * - URL language is detected from the requested URL and will be used to rewrite
 *   URLs appearing in the page being rendered. If no language can be detected,
 *   there are two possibilities:
 *   - If the default language has no configured path prefix or domain, then the
 *     default language is used. This guarantees that (missing) URL prefixes are
 *     preserved when navigating through the site.
 *   - If the default language has a configured path prefix or domain, a
 *     requested URL having an empty prefix or domain is an anomaly that must be
 *     fixed. This is done by introducing a prefix or domain in the rendered
 *     page matching the detected interface language.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrlFallback::METHOD_ID,
 *   types = {Drupal\Core\Language\Language::TYPE_URL},
 *   weight = 8,
 *   name = @Translation("URL fallback"),
 *   description = @Translation("Use an already detected language for URLs if none is found.")
 * )
 */
class LanguageNegotiationUrlFallback extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-url-fallback';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    $default = $this->languageManager->getLanguageDefault();
    $prefix = ($this->config['url']['source'] == LanguageNegotiationUrl::CONFIG_PATH_PREFIX);

    // If the default language is not configured to convey language information,
    // a missing URL language information indicates that URL language should be
    // the default one, otherwise we fall back to an already detected language.
    $domains = $this->config['url']['domains'];
    $prefixes = $this->config['url']['prefixes'];
    if (($prefix && empty($prefixes[$default->id])) || (!$prefix && empty($domains[$default->id]))) {
      return $default->id;
    }
    else {
      return $this->languageManager->getLanguage()->id;
    }
  }

}
