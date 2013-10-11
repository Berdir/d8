<?php

use Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationSession;

/**
 * @file
 * Language negotiation functions.
 */

/**
 * Reads language prefixes and uses the langcode if no prefix is set.
 */
function language_negotiation_url_prefixes() {
  return \Drupal::config('language.negotiation')->get('url.prefixes');
}

/**
 * Update the list of prefixes from the installed languages.
 */
function language_negotiation_url_prefixes_update() {
  $prefixes = language_negotiation_url_prefixes();
  foreach (language_list() as $language) {
    // The prefix for this language should be updated if it's not assigned yet
    // or the prefix is set to the empty string.
    if (empty($prefixes[$language->id])) {
      // For the default language, set the prefix to the empty string,
      // otherwise use the langcode.
      $prefixes[$language->id] = !empty($language->default) ? '' : $language->id;
    }
    // Otherwise we keep the configured prefix.
  }
  language_negotiation_url_prefixes_save($prefixes);
}

/**
 * Saves language prefix settings.
 */
function language_negotiation_url_prefixes_save(array $prefixes) {
  \Drupal::config('language.negotiation')
    ->set('url.prefixes', $prefixes)
    ->save();
}

/**
 * Reads language domains.
 */
function language_negotiation_url_domains() {
  return \Drupal::config('language.negotiation')->get('url.domains');
}

/**
 * Saves the language domain settings.
 */
function language_negotiation_url_domains_save(array $domains) {
  \Drupal::config('language.negotiation')
    ->set('url.domains', $domains)
    ->save();
}

/**
 * Rewrite URLs for the Session language negotiation method.
 */
function language_url_rewrite_session(&$path, &$options) {
  static $query_rewrite, $query_param, $query_value;

  // The following values are not supposed to change during a single page
  // request processing.
  if (!isset($query_rewrite)) {
    global $user;
    if (!$user->id()) {
      $languages = language_list();
      $query_param = check_plain(\Drupal::config('language.negotiation')->get('session.parameter'));
      $query_value = isset($_GET[$query_param]) ? check_plain($_GET[$query_param]) : NULL;
      $query_rewrite = isset($languages[$query_value]) && language_negotiation_method_enabled(LanguageNegotiationSession::METHOD_ID);
    }
    else {
      $query_rewrite = FALSE;
    }
  }

  // If the user is anonymous, the user language negotiation method is enabled,
  // and the corresponding option has been set, we must preserve any explicit
  // user language preference even with cookies disabled.
  if ($query_rewrite) {
    if (is_string($options['query'])) {
      $query = array();
      parse_str($options['query'], $query);
      $options['query'] = $query;
    }
    if (!isset($options['query'][$query_param])) {
      $options['query'][$query_param] = $query_value;
    }
  }
}
