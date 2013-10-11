<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUserAdmin.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Identifies admin language from the user preferences.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUserAdmin::METHOD_ID,
 *   types = {Drupal\Core\Language\Language::TYPE_INTERFACE},
 *   weight = 10,
 *   name = @Translation("Account administration pages"),
 *   description = @Translation("Account administration pages language setting.")
 * )
 */
class LanguageNegotiationUserAdmin extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-user-admin';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    // User preference (only for authenticated users).
    $user = \Drupal::currentUser();

    // @todo Avoid calling _current_path() and path_is_admin() directly.
    $request_path = $request ? urldecode(trim($request->getPathInfo(), '/')) : _current_path();
    if ($user->isAuthenticated() && path_is_admin($request_path)) {
      $langcode = $user->getPreferredAdminLangcode();
      $default_langcode = $this->languageManager->getLanguageDefault()->id;
      if (!empty($langcode) && $langcode != $default_langcode && isset($languages[$langcode])) {
        return $langcode;
      }
    }

    // No language preference from the user or not on an admin path.
    return FALSE;
  }

}
