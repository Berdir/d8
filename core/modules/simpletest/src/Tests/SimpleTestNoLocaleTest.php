<?php

/**
 * @file
 * Contains \Drupal\simpletest\Tests\SimpleTestNoLocaleTest.
 */

namespace Drupal\simpletest\Tests;

use Drupal\Component\Utility\Crypt;
use Drupal\simpletest\WebTestBase;

/**
 * A helper test that is run in a child site without locale.
 *
 * This test is executed by Drupal\simpletest\Tests\SimpleTestWithLocaleTest.
 *
 * @group simpletest
 */
class SimpleTestNoLocaleTest extends WebTestBase {

  /**
   * Ensures the tests run well no  language module enabled.
   */
  protected function testSimpleTestWithLanguageModule() {
    if ($this->isInChildSite()) {
      // Ensure the .htkey file exists since this is only created just before a
      // request. This allows the stub test to make requests. The event does not
      // fire here and drupal_generate_test_ua() can not generate a key for a
      // test in a test since the prefix has changed.
      // @see \Drupal\Core\Test\HttpClientMiddleware\TestHttpClientMiddleware::onBeforeSendRequest()
      // @see drupal_generate_test_ua();
      $key_file = DRUPAL_ROOT . '/sites/simpletest/' . substr($this->databasePrefix, 10) . '/.htkey';
      $private_key = Crypt::randomBytesBase64(55);
      file_put_contents($key_file, $private_key);
    }
    $this->drupalLogin($this->drupalCreateUser());
  }

}
