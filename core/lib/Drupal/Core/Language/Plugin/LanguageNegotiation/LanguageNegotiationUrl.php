<?php

/**
 * @file
 * Contains \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrl.
 */

namespace Drupal\Core\Language\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language via URL prefix or domain.
 *
 * @Plugin(
 *   id = Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrl::METHOD_ID,
 *   types = {Drupal\Core\Language\Language::TYPE_INTERFACE, Drupal\Core\Language\Language::TYPE_CONTENT, Drupal\Core\Language\Language::TYPE_URL},
 *   weight = -8,
 *   name = @Translation("URL"),
 *   description = @Translation("Language from the URL (Path prefix or domain)."),
 *   config = "admin/config/regional/language/detection/url"
 * )
 */
class LanguageNegotiationUrl extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-url';

  /**
   * URL language negotiation: use the path prefix as URL language indicator.
   */
  const CONFIG_PATH_PREFIX = 'path_prefix';

  /**
   * URL language negotiation: use the domain as URL language indicator.
   */
  const CONFIG_DOMAIN = 'domain';

  /**
   * {@inheritdoc}
   */
  public function negotiateLanguage(array $languages, Request $request = NULL) {
    if (!$request) {
      return FALSE;
    }

    $langcode = FALSE;
    switch ($this->config['url']['source']) {
      case LanguageNegotiationUrl::CONFIG_PATH_PREFIX:
        $request_path = urldecode(trim($request->getPathInfo(), '/'));
        $path_args = explode('/', $request_path);
        $prefix = array_shift($path_args);

        // Search prefix within enabled languages.
        $prefixes = $this->config['url']['prefixes'];
        $negotiated_language = FALSE;
        foreach ($languages as $language) {
          if (isset($prefixes[$language->id]) && $prefixes[$language->id] == $prefix) {
            $negotiated_language = $language;
            break;
          }
        }

        if ($negotiated_language !== FALSE) {
          $langcode = $negotiated_language->id;
        }
        break;

      case LanguageNegotiationUrl::CONFIG_DOMAIN:
        // Get only the host, not the port.
        $http_host = $request->server->get('HTTP_HOST');
        if (strpos($http_host, ':') !== FALSE) {
          $http_host_tmp = explode(':', $http_host);
          $http_host = current($http_host_tmp);
        }
        $domains = $this->config['url']['domains'];
        foreach ($languages as $language) {
          // Skip the check if the language doesn't have a domain.
          if (!empty($domains[$language->id])) {
            // Ensure that there is exactly one protocol in the URL when
            // checking the hostname.
            $host = 'http://' . str_replace(array('http://', 'https://'), '', $domains[$language->id]);
            $host = parse_url($host, PHP_URL_HOST);
            if ($http_host == $host) {
              $langcode = $language->id;
              break;
            }
          }
        }
        break;
    }

    return $langcode;
  }

  /**
   * Return links for the URL language switcher block.
   *
   * Translation links may be provided by other modules.
   */
  function languageSwitchLinks($type, $path) {
    $languages = language_list();
    $links = array();

    foreach ($languages as $language) {
      $links[$language->id] = array(
        'href'       => $path,
        'title'      => $language->name,
        'language'   => $language,
        'attributes' => array('class' => array('language-link')),
      );
    }

    return $links;
  }

}
