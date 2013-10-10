<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationSession.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Identify language from a request/session parameter.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationSession::METHOD_ID,
 *   weight = -6,
 *   name = @Translation("Session"),
 *   description = @Translation("Language from a request/session parameter."),
 *   config = "admin/config/regional/language/detection/session"
 * )
 */
class LanguageNegotiationSession extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-session';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    $param = $this->config['session']['parameter'];

    // Request parameter: we need to update the session parameter only if we
    // have an authenticated user.
    $langcode = $request->query->get($param);
    if ($langcode) {
      global $user;
      $languages = $this->languageManager->getLanguageList();
      if ($user->isAuthenticated() && isset($languages[$langcode])) {
        $_SESSION[$param] = $langcode;
      }
    }

    // Session parameter.
    if (isset($_SESSION[$param])) {
      return $_SESSION[$param];
    }

    return FALSE;
  }

  /**
   * Return the session language switcher block.
   */
  function languageSwitchLinks($type, $path) {
    $param = \Drupal::config('language.negotiation')->get('session.parameter');
    $language_query = isset($_SESSION[$param]) ? $_SESSION[$param] : language($type)->id;

    $languages = language_list();
    $links = array();

    $query = $_GET;

    foreach ($languages as $language) {
      $langcode = $language->id;
      $links[$langcode] = array(
        'href'       => $path,
        'title'      => $language->name,
        'attributes' => array('class' => array('language-link')),
        'query'      => $query,
      );
      if ($language_query != $langcode) {
        $links[$langcode]['query'][$param] = $langcode;
      }
      else {
        $links[$langcode]['attributes']['class'][] = ' session-active';
      }
    }

    return $links;
  }

}
