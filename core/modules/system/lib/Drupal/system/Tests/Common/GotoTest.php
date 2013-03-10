<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Common\GotoTest.
 */

namespace Drupal\system\Tests\Common;

use Drupal\simpletest\WebTestBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Tests RedirectResponse and hook_redirect_response_alter().
 */
class GotoTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('common_test');

  public static function getInfo() {
    return array(
      'name' => 'Redirect functionality',
      'description' => 'Tests the RedirectResponse and hook_redirect_response_alter() functionality.',
      'group' => 'Common',
    );
  }

  /**
   * Tests RedirectResponse.
   */
  function testRedirectResponse() {
    $this->drupalGet('common-test/redirect-response/redirect');
    $headers = $this->drupalGetHeaders(TRUE);
    list(, $status) = explode(' ', $headers[0][':status'], 3);
    $this->assertEqual($status, 302, 'Expected response code was sent.');
    $this->assertText('RedirectResponse', 'Drupal RedirectResponse redirect succeeded.');
    $this->assertEqual($this->getUrl(), url('common-test/redirect-response', array('absolute' => TRUE)), 'Drupal RedirectResponse redirected to expected URL.');

    $this->drupalGet('common-test/redirect-response/redirect_advanced');
    $headers = $this->drupalGetHeaders(TRUE);
    list(, $status) = explode(' ', $headers[0][':status'], 3);
    $this->assertEqual($status, 301, 'Expected response code was sent.');
    $this->assertText('RedirectResponse', 'Drupal RedirectResponse redirect succeeded.');
    $this->assertEqual($this->getUrl(), url('common-test/redirect-response', array('query' => array('foo' => '123'), 'absolute' => TRUE)), 'Drupal RedirectResponse redirected to expected URL.');

    // Test that RedirectResponse respects ?destination=xxx. Use a complicated URL
    // to test that the path is encoded and decoded properly.
    $destination = 'common-test/redirect-response/destination?foo=%2525&bar=123';
    $this->drupalGet('common-test/redirect-response/redirect', array('query' => array('destination' => $destination)));
    $this->assertText('RedirectResponse', 'Drupal RedirectResponse redirect with destination succeeded.');
    $this->assertEqual($this->getUrl(), url('common-test/redirect-response/destination', array('query' => array('foo' => '%25', 'bar' => '123'), 'absolute' => TRUE)), 'Drupal RedirectResponse redirected to given query string destination.');

    // Test that RedirectResponse respects ?destination=xxx with an absolute URL
    // that points to this Drupal installation.
    $destination = url('common-test/redirect-response/alternative', array('absolute' => TRUE));
    $this->drupalGet('common-test/redirect-response/redirect', array('query' => array('destination' => $destination)));
    $this->assertText('RedirectResponse_alternative', 'Drupal RedirectResponse redirect with absolute URL destination that points to this Drupal installation succeeded.');
    $this->assertEqual($this->getUrl(), url('common-test/redirect-response/alternative', array('absolute' => TRUE)), 'Drupal RedirectResponse redirected to given query string destination with absolute URL that points to this Drupal installation.');

    // Test that RedirectResponse fails to respect ?destination=xxx with an absolute URL
    // that does not point to this Drupal installation.
    $destination = 'http://example.com';
    $this->drupalGet('common-test/redirect-response/redirect', array('query' => array('destination' => $destination)));
    $this->assertText('RedirectResponse', 'Drupal RedirectResponse fails to redirect with absolute URL destination that does not point to this Drupal installation.');
    $this->assertNotEqual($this->getUrl(), $destination, 'Drupal RedirectResponse failed to redirect to given query string destination with absolute URL that does not point to this Drupal installation.');
  }

  /**
   * Tests hook_redirect_response_alter().
   */
  function testDrupalRedirectResponseAlter() {
    $this->drupalGet('common-test/redirect-response/redirect_fail');

    $this->assertNoText(t("Drupal RedirectResponse failed to stop program"), 'Drupal RedirectResponse stopped program.');
    $this->assertNoText('RedirectResponse_fail', 'Drupal RedirectResponse redirect failed.');
  }

  /**
   * Tests drupal_get_destination().
   */
  function testDrupalGetDestination() {
    $query = $this->randomName(10);

    // Verify that a 'destination' query string is used as destination.
    $this->drupalGet('common-test/destination', array('query' => array('destination' => $query)));
    $this->assertText('The destination: ' . $query, 'The given query string destination is determined as destination.');

    // Verify that the current path is used as destination.
    $this->drupalGet('common-test/destination', array('query' => array($query => NULL)));
    $url = 'common-test/destination?' . $query;
    $this->assertText('The destination: ' . $url, 'The current path is determined as destination.');
  }
}
