<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrl.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from the user preferences.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUser::METHOD_ID,
 *   weight = -4,
 *   name = @Translation("User"),
 *   description = @Translation("Follow the user's language preference.")
 * )
 */
class LanguageNegotiationUser extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-user';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    // User preference (only for authenticated users).
    $user = \Drupal::currentUser();

    if ($user->isAuthenticated()) {
      $langcode = $user->getPreferredLangcode();
      $default_langcode = $this->languageManager->getLanguageDefault()->id;
      if (!empty($langcode) && $langcode != $default_langcode && isset($languages[$langcode])) {
        return $langcode;
      }
    }

    // No language preference from the user.
    return FALSE;
  }

}
