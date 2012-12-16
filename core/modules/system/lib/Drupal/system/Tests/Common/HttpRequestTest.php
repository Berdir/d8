<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Common\HttpRequestTest.
 */

namespace Drupal\system\Tests\Common;

use Drupal\simpletest\WebTestBase;
use Drupal\Core\Language\Language;

/**
 * Tests drupal_http_request().
 */
class HttpRequestTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system_test', 'language', 'test_page_test');

  public static function getInfo() {
    return array(
      'name' => 'Drupal HTTP request',
      'description' => "Performs tests on Drupal's HTTP request mechanism.",
      'group' => 'Common',
    );
  }

  /**
   * Checks HTTP requests.
   */
  function testDrupalHTTPRequest() {
    global $is_https;

    // Parse URL schema.
    $missing_scheme = drupal_http_request('example.com/path');
    $this->assertEqual($missing_scheme->code, -1002, 'Returned with "-1002" error code.');
    $this->assertEqual($missing_scheme->error, 'missing schema', 'Returned with "missing schema" error message.');

    $unable_to_parse = drupal_http_request('http:///path');
    $this->assertEqual($unable_to_parse->code, -1001, 'Returned with "-1001" error code.');
    $this->assertEqual($unable_to_parse->error, 'unable to parse URL', 'Returned with "unable to parse URL" error message.');

    // Fetch the test page.
    $result = drupal_http_request(url('test-page', array('absolute' => TRUE)));
    $this->assertEqual($result->code, 200, 'Fetched page successfully.');
    $this->drupalSetContent($result->data);
    $this->assertTitle(t('Test page | @site-name', array('@site-name' => config('system.site')->get('name'))));

    // Test that Drupal.settings is properly parsed.
    $settings = $this->drupalGetSettings();
    $this->assertIdentical($settings['test-setting'], 'azAZ09();.,\\\/-_{}');

    // Test that code and status message is returned.
    $result = drupal_http_request(url('pagedoesnotexist', array('absolute' => TRUE)));
    $this->assertTrue(!empty($result->protocol), 'Result protocol is returned.');
    $this->assertEqual($result->code, '404', 'Result code is 404');
    $this->assertEqual($result->status_message, 'Not Found', 'Result status message is "Not Found"');

    // Skip the timeout tests when the testing environment is HTTPS because
    // stream_set_timeout() does not work for SSL connections.
    // @link http://bugs.php.net/bug.php?id=47929
    if (!$is_https) {
      // Test that timeout is respected. The test machine is expected to be able
      // to make the connection (i.e. complete the fsockopen()) in 2 seconds and
      // return within a total of 5 seconds. If the test machine is extremely
      // slow, the test will fail. fsockopen() has been seen to time out in
      // slightly less than the specified timeout, so allow a little slack on
      // the minimum expected time (i.e. 1.8 instead of 2).
      timer_start(__METHOD__);
      $result = drupal_http_request(url('system-test/sleep/10', array('absolute' => TRUE)), array('timeout' => 2));
      $time = timer_read(__METHOD__) / 1000;
      $this->assertTrue(1.8 < $time && $time < 5, format_string('Request timed out (%time seconds).', array('%time' => $time)));
      $this->assertTrue($result->error, 'An error message was returned.');
      $this->assertEqual($result->code, HTTP_REQUEST_TIMEOUT, 'Proper error code was returned.');
    }
  }

  /**
   * Tests HTTP basic authorization.
   */
  function testDrupalHTTPRequestBasicAuth() {
    $username = $this->randomName();
    $password = $this->randomName();
    $url = url('system-test/auth', array('absolute' => TRUE));

    $auth = str_replace('://', '://' . $username . ':' . $password . '@', $url);
    $result = drupal_http_request($auth);

    $this->drupalSetContent($result->data);
    $this->assertRaw($username, '$_SERVER["PHP_AUTH_USER"] is passed correctly.');
    $this->assertRaw($password, '$_SERVER["PHP_AUTH_PW"] is passed correctly.');
  }

  /**
   * Tests HTTP redirect requests.
   */
  function testDrupalHTTPRequestRedirect() {
    $redirect_301 = drupal_http_request(url('system-test/redirect/301', array('absolute' => TRUE)), array('max_redirects' => 1));
    $this->assertEqual($redirect_301->redirect_code, 301, 'drupal_http_request follows the 301 redirect.');

    $redirect_301 = drupal_http_request(url('system-test/redirect/301', array('absolute' => TRUE)), array('max_redirects' => 0));
    $this->assertFalse(isset($redirect_301->redirect_code), 'drupal_http_request does not follow 301 redirect if max_redirects = 0.');

    $redirect_invalid = drupal_http_request(url('system-test/redirect-noscheme', array('absolute' => TRUE)), array('max_redirects' => 1));
    $this->assertEqual($redirect_invalid->code, -1002, format_string('301 redirect to invalid URL returned with error code !error.', array('!error' => $redirect_invalid->error)));
    $this->assertEqual($redirect_invalid->error, 'missing schema', format_string('301 redirect to invalid URL returned with error message "!error".', array('!error' => $redirect_invalid->error)));

    $redirect_invalid = drupal_http_request(url('system-test/redirect-noparse', array('absolute' => TRUE)), array('max_redirects' => 1));
    $this->assertEqual($redirect_invalid->code, -1001, format_string('301 redirect to invalid URL returned with error message code "!error".', array('!error' => $redirect_invalid->error)));
    $this->assertEqual($redirect_invalid->error, 'unable to parse URL', format_string('301 redirect to invalid URL returned with error message "!error".', array('!error' => $redirect_invalid->error)));

    $redirect_invalid = drupal_http_request(url('system-test/redirect-invalid-scheme', array('absolute' => TRUE)), array('max_redirects' => 1));
    $this->assertEqual($redirect_invalid->code, -1003, format_string('301 redirect to invalid URL returned with error code !error.', array('!error' => $redirect_invalid->error)));
    $this->assertEqual($redirect_invalid->error, 'invalid schema ftp', format_string('301 redirect to invalid URL returned with error message "!error".', array('!error' => $redirect_invalid->error)));

    $redirect_302 = drupal_http_request(url('system-test/redirect/302', array('absolute' => TRUE)), array('max_redirects' => 1));
    $this->assertEqual($redirect_302->redirect_code, 302, 'drupal_http_request follows the 302 redirect.');

    $redirect_302 = drupal_http_request(url('system-test/redirect/302', array('absolute' => TRUE)), array('max_redirects' => 0));
    $this->assertFalse(isset($redirect_302->redirect_code), 'drupal_http_request does not follow 302 redirect if $retry = 0.');

    $redirect_307 = drupal_http_request(url('system-test/redirect/307', array('absolute' => TRUE)), array('max_redirects' => 1));
    $this->assertEqual($redirect_307->redirect_code, 307, 'drupal_http_request follows the 307 redirect.');

    $redirect_307 = drupal_http_request(url('system-test/redirect/307', array('absolute' => TRUE)), array('max_redirects' => 0));
    $this->assertFalse(isset($redirect_307->redirect_code), 'drupal_http_request does not follow 307 redirect if max_redirects = 0.');

    $multiple_redirect_final_url = url('system-test/multiple-redirects/0', array('absolute' => TRUE));
    $multiple_redirect_1 = drupal_http_request(url('system-test/multiple-redirects/1', array('absolute' => TRUE)), array('max_redirects' => 1));
    $this->assertEqual($multiple_redirect_1->redirect_url, $multiple_redirect_final_url, 'redirect_url contains the final redirection location after 1 redirect.');

    $multiple_redirect_3 = drupal_http_request(url('system-test/multiple-redirects/3', array('absolute' => TRUE)), array('max_redirects' => 3));
    $this->assertEqual($multiple_redirect_3->redirect_url, $multiple_redirect_final_url, 'redirect_url contains the final redirection location after 3 redirects.');
  }

  /**
   * Tests Content-language headers generated by Drupal.
   */
  function testDrupalHTTPRequestHeaders() {
    // Check the default header.
    $request = drupal_http_request(url('<front>', array('absolute' => TRUE)));
    $this->assertEqual($request->headers['content-language'], 'en', 'Content-Language HTTP header is English.');

    // Add French language.
    $language = new Language(array(
      'langcode' => 'fr',
      'name' => 'French',
    ));
    language_save($language);

    // Request front page in French and check for matching Content-language.
    $request = drupal_http_request(url('<front>', array('absolute' => TRUE, 'language' => $language)));
    $this->assertEqual($request->headers['content-language'], 'fr', 'Content-Language HTTP header is French.');
  }
}
