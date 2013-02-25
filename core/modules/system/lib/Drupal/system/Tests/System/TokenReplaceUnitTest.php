<?php

/**
 * @file
 * Contains Drupal\system\Tests\System\TokenReplaceUnitTest.
 */

namespace Drupal\system\Tests\System;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Test token replacement in strings.
 */
class TokenReplaceUnitTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system');

  public static function getInfo() {
    return array(
      'name' => 'Token replacement unit test',
      'description' => 'Generates text using placeholders for dummy content to check token replacement.',
      'group' => 'System',
    );
  }

  public function setUp() {
    parent::setUp();
    // Install default system configuration.
    config_install_default_config('module', 'system');
  }

  /**
   * Test whether token-replacement works in various contexts.
   */
  function testSystemTokenRecognition() {
    $language_interface = language(LANGUAGE_TYPE_INTERFACE);

    // Generate prefixes and suffixes for the token context.
    $tests = array(
      array('prefix' => 'this is the ', 'suffix' => ' site'),
      array('prefix' => 'this is the', 'suffix' => 'site'),
      array('prefix' => '[', 'suffix' => ']'),
      array('prefix' => '', 'suffix' => ']]]'),
      array('prefix' => '[[[', 'suffix' => ''),
      array('prefix' => ':[:', 'suffix' => '--]'),
      array('prefix' => '-[-', 'suffix' => ':]:'),
      array('prefix' => '[:', 'suffix' => ']'),
      array('prefix' => '[site:', 'suffix' => ':name]'),
      array('prefix' => '[site:', 'suffix' => ']'),
    );

    // Check if the token is recognized in each of the contexts.
    foreach ($tests as $test) {
      $input = $test['prefix'] . '[site:name]' . $test['suffix'];
      $expected = $test['prefix'] . 'Drupal' . $test['suffix'];
      $output = token_replace($input, array(), array('langcode' => $language_interface->langcode));
      $this->assertTrue($output == $expected, format_string('Token recognized in string %string', array('%string' => $input)));
    }
  }

  /**
   * Tests the generation of all system site information tokens.
   */
  function testSystemSiteTokenReplacement() {
    // The use of the url() function requires the url_alias table to exist.
    $this->installSchema('system', 'url_alias');

    $language_interface = language(LANGUAGE_TYPE_INTERFACE);
    $url_options = array(
      'absolute' => TRUE,
      'language' => $language_interface,
    );

    // Set a few site variables.
    config('system.site')
      ->set('name', '<strong>Drupal<strong>')
      ->set('slogan', '<blink>Slogan</blink>')
      ->set('mail', 'simpletest@example.com')
      ->save();

    // Generate and test sanitized tokens.
    $tests = array();
    $tests['[site:name]'] = check_plain(config('system.site')->get('name'));
    $tests['[site:slogan]'] = filter_xss_admin(config('system.site')->get('slogan'));
    $tests['[site:mail]'] = config('system.site')->get('mail');
    $tests['[site:url]'] = url('<front>', $url_options);
    $tests['[site:url-brief]'] = preg_replace(array('!^https?://!', '!/$!'), '', url('<front>', $url_options));
    $tests['[site:login-url]'] = url('user', $url_options);

    // Test to make sure that we generated something for each token.
    $this->assertFalse(in_array(0, array_map('strlen', $tests)), 'No empty tokens generated.');

    foreach ($tests as $input => $expected) {
      $output = token_replace($input, array(), array('langcode' => $language_interface->langcode));
      $this->assertEqual($output, $expected, format_string('Sanitized system site information token %token replaced.', array('%token' => $input)));
    }

    // Generate and test unsanitized tokens.
    $tests['[site:name]'] = config('system.site')->get('name');
    $tests['[site:slogan]'] = config('system.site')->get('slogan');

    foreach ($tests as $input => $expected) {
      $output = token_replace($input, array(), array('langcode' => $language_interface->langcode, 'sanitize' => FALSE));
      $this->assertEqual($output, $expected, format_string('Unsanitized system site information token %token replaced.', array('%token' => $input)));
    }
  }

  /**
   * Tests the generation of all system date tokens.
   */
  function testSystemDateTokenReplacement() {
    $language_interface = language(LANGUAGE_TYPE_INTERFACE);

    // Set time to one hour before request.
    $date = REQUEST_TIME - 3600;

    // Generate and test tokens.
    $tests = array();
    $tests['[date:short]'] = format_date($date, 'short', '', NULL, $language_interface->langcode);
    $tests['[date:medium]'] = format_date($date, 'medium', '', NULL, $language_interface->langcode);
    $tests['[date:long]'] = format_date($date, 'long', '', NULL, $language_interface->langcode);
    $tests['[date:custom:m/j/Y]'] = format_date($date, 'custom', 'm/j/Y', NULL, $language_interface->langcode);
    $tests['[date:since]'] = format_interval((REQUEST_TIME - $date), 2, $language_interface->langcode);
    $tests['[date:raw]'] = filter_xss($date);

    // Test to make sure that we generated something for each token.
    $this->assertFalse(in_array(0, array_map('strlen', $tests)), 'No empty tokens generated.');

    foreach ($tests as $input => $expected) {
      $output = token_replace($input, array('date' => $date), array('langcode' => $language_interface->langcode));
      $this->assertEqual($output, $expected, format_string('Date token %token replaced.', array('%token' => $input)));
    }
  }
}
