<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Language\LanguageNegotiationUrlTest.
 */

namespace Drupal\Tests\Core\Language;

use Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrl;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the URL and domain language negotiation.
 *
 * @see \Drupal\Core\Language\Plugin\LanguageNegotiation\LanguageNegotiationUrl
 */
class LanguageNegotiationUrlTest extends UnitTestCase {

  /**
   * Test domain language negotiation.
   *
   * @dataProvider providerTestDomain
   */
  public function testDomain($http_host, $domains, $languages, $expected_langcode) {
    $config = array(
      'url' => array(
        'source' => LanguageNegotiationUrl::CONFIG_DOMAIN,
        'domains' => $domains,
      ),
    );

    $request = Request::create('', 'GET', array(), array(), array(), array('HTTP_HOST' => $http_host));
    $negotiation = new LanguageNegotiationUrl($config);
    $this->assertEquals($expected_langcode, $negotiation->negotiateLanguage($languages, $request));
  }

  /**
   * Provides data for the domain test.
   *
   * @return array
   *   An array of data for checking domain negotation.
   */
  public function providerTestDomain() {
    $default_languages = array(
      'de' => (object) array(
        'id' => 'de',
      ),
      'en' => (object) array(
        'id' => 'en',
      ),
    );

    $domain_configuration[] = array(
      'http_host' => 'example.de',
      'domains' => array(
        'de' => 'http://example.de',
      ),
      'languages' => $default_languages,
      'expected_langocde' => 'de',
    );
    // No configuration.
    $domain_configuration[] = array(
      'http_host' => 'example.de',
      'domains' => array(),
      'languages' => $default_languages,
      'expected_langocde' => FALSE,
    );
    // HTTP host with a port.
    $domain_configuration[] = array(
      'http_host' => 'example.de:8080',
      'domains' => array(
        'de' => 'http://example.de',
      ),
      'languages' => $default_languages,
      'expected_langocde' => 'de',
    );
    // Domain configuration with https://.
    $domain_configuration[] = array(
      'http_host' => 'example.de',
      'domains' => array(
        'de' => 'https://example.de',
      ),
      'languages' => $default_languages,
      'expected_langocde' => 'de',
    );
    // Non-matching HTTP host.
    $domain_configuration[] = array(
      'http_host' => 'example.com',
      'domains' => array(
        'de' => 'http://example.com',
      ),
      'languages' => $default_languages,
      'expected_langocde' => 'de',
    );
    // Testing a non-existing language.
    $domain_configuration[] = array(
      'http_host' => 'example.com',
      'domains' => array(
        'de' => 'http://example.com',
      ),
      'languages' => array(
        'en' => (object) array(
          'id' => 'en',
        ),
      ),
      'expected_langocde' => FALSE,
    );
    // Multiple domain configurations.
    $domain_configuration[] = array(
      'http_host' => 'example.com',
      'domains' => array(
        'de' => 'http://example.de',
        'en' => 'http://example.com',
      ),
      'languages' => $default_languages,
      'expected_langocde' => 'en',
    );
    return $domain_configuration;
  }
}
