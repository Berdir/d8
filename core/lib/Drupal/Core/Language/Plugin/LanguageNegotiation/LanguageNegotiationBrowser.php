<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationBrowser.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from the Accept-language HTTP header we got.
 *
 * The algorithm works as follows:
 * - map browser language codes to Drupal language codes.
 * - order all browser language codes by qvalue from high to low.
 * - add generic browser language codes if they aren't already specified
 *   but with a slightly lower qvalue.
 * - find the most specific Drupal language code with the highest qvalue.
 * - if 2 or more languages are having the same qvalue, respect the order of
 *   them inside the $languages array.
 *
 * We perform browser accept-language parsing only if page cache is disabled,
 * otherwise we would cache a user-specific preference.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationBrowser::METHOD_ID,
 *   weight = -2,
 *   cache = 0,
 *   name = @Translation("Browser"),
 *   description = @Translation("Language from the browser's language settings."),
 *   config = "admin/config/regional/language/detection/browser"
 * )
 */
class LanguageNegotiationBrowser extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-browser';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    if (!$request || !$request->server->get('HTTP_ACCEPT_LANGUAGE')) {
      return FALSE;
    }

    // The Accept-Language header contains information about the language
    // preferences configured in the user's browser / operating system. RFC 2616
    // (section 14.4) defines the Accept-Language header as follows:
    //   Accept-Language = "Accept-Language" ":"
    //                  1#( language-range [ ";" "q" "=" qvalue ] )
    //   language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
    // Samples: "hu, en-us;q=0.66, en;q=0.33", "hu,en-us;q=0.5"
    $browser_langcodes = array();
    if (preg_match_all('@(?<=[, ]|^)([a-zA-Z-]+|\*)(?:;q=([0-9.]+))?(?:$|\s*,\s*)@', trim($request->server->get('HTTP_ACCEPT_LANGUAGE')), $matches, PREG_SET_ORDER)) {
      // Load custom mappings to support browsers that are sending non standard
      // language codes.
      $mappings = $this->config['browser']['mappings'];

      foreach ($matches as $match) {
        if ($mappings) {
          $langcode = strtolower($match[1]);
          foreach ($mappings as $browser_langcode => $drupal_langcode) {
            if ($langcode == $browser_langcode) {
              $match[1] = $drupal_langcode;
            }
          }
        }
        // We can safely use strtolower() here, tags are ASCII.
        // RFC2616 mandates that the decimal part is no more than three digits,
        // so we multiply the qvalue by 1000 to avoid floating point comparisons.
        $langcode = strtolower($match[1]);
        $qvalue = isset($match[2]) ? (float) $match[2] : 1;
        // Take the highest qvalue for this langcode. Although the request
        // supposedly contains unique langcodes, our mapping possibly resolves
        // to the same langcode for different qvalues. Keep the highest.
        $browser_langcodes[$langcode] = max(
          (int) ($qvalue * 1000),
          (isset($browser_langcodes[$langcode]) ? $browser_langcodes[$langcode] : 0)
        );
      }
    }

    // We should take pristine values from the HTTP headers, but Internet
    // Explorer from version 7 sends only specific language tags (eg. fr-CA)
    // without the corresponding generic tag (fr) unless explicitly configured.
    // In that case, we assume that the lowest value of the specific tags is the
    // value of the generic language to be as close to the HTTP 1.1 spec as
    // possible.
    // See http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4 and
    // http://blogs.msdn.com/b/ie/archive/2006/10/17/accept-language-header-for-internet-explorer-7.aspx
    asort($browser_langcodes);
    foreach ($browser_langcodes as $langcode => $qvalue) {
      // For Chinese languages the generic tag is either zh-hans or zh-hant, so
      // we need to handle this separately, we can not split $langcode on the
      // first occurence of '-' otherwise we get a non-existing language zh.
      // All other languages use a langcode without a '-', so we can safely
      // split on the first occurence of it.
      $generic_tag = '';
      if (strlen($langcode) > 7 && (substr($langcode, 0, 7) == 'zh-hant' || substr($langcode, 0, 7) == 'zh-hans')) {
        $generic_tag = substr($langcode, 0, 7);
      }
      else {
        $generic_tag = strtok($langcode, '-');
      }
      if (!empty($generic_tag) && !isset($browser_langcodes[$generic_tag])) {
        // Add the generic langcode, but make sure it has a lower qvalue as the
        // more specific one, so the more specific one gets selected if it's
        // defined by both the browser and Drupal.
        $browser_langcodes[$generic_tag] = $qvalue - 0.1;
      }
    }

    // Find the enabled language with the greatest qvalue, following the rules
    // of RFC 2616 (section 14.4). If several languages have the same qvalue,
    // prefer the one with the greatest weight.
    $best_match_langcode = FALSE;
    $max_qvalue = 0;
    foreach ($languages as $langcode => $language) {
      // Language tags are case insensitive (RFC2616, sec 3.10).
      $langcode = strtolower($langcode);

      // If nothing matches below, the default qvalue is the one of the wildcard
      // language, if set, or is 0 (which will never match).
      $qvalue = isset($browser_langcodes['*']) ? $browser_langcodes['*'] : 0;

      // Find the longest possible prefix of the browser-supplied language ('the
      // language-range') that matches this site language ('the language tag').
      $prefix = $langcode;
      do {
        if (isset($browser_langcodes[$prefix])) {
          $qvalue = $browser_langcodes[$prefix];
          break;
        }
      }
      while ($prefix = substr($prefix, 0, strrpos($prefix, '-')));

      // Find the best match.
      if ($qvalue > $max_qvalue) {
        $best_match_langcode = $language->id;
        $max_qvalue = $qvalue;
      }
    }

    return $best_match_langcode;
  }

}
