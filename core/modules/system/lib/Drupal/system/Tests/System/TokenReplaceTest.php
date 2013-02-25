<?php

/**
 * @file
 * Definition of Drupal\system\Tests\System\TokenReplaceTest.
 */

namespace Drupal\system\Tests\System;

use Drupal\simpletest\WebTestBase;

/**
 * Test token replacement in strings.
 */
class TokenReplaceTest extends WebTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Node and user token replacement',
      'description' => 'Generates a node and a user to check token replacement.',
      'group' => 'System',
    );
  }

  /**
   * Creates a user and a node, then tests the tokens generated from them.
   */
  function testTokenReplacement() {
    // Create the initial objects.
    $account = $this->drupalCreateUser();
    $node = $this->drupalCreateNode(array('uid' => $account->uid));
    $node->title = '<blink>Blinking Text</blink>';
    global $user;
    $language_interface = language(LANGUAGE_TYPE_INTERFACE);

    $source  = '[node:title]';         // Title of the node we passed in
    $source .= '[node:author:name]';   // Node author's name
    $source .= '[node:created:since]'; // Time since the node was created
    $source .= '[current-user:name]';  // Current user's name
    $source .= '[date:short]';         // Short date format of REQUEST_TIME
    $source .= '[user:name]';          // No user passed in, should be untouched
    $source .= '[bogus:token]';        // Non-existent token

    $target  = check_plain($node->title);
    $target .= check_plain($account->name);
    $target .= format_interval(REQUEST_TIME - $node->created, 2, $language_interface->langcode);
    $target .= check_plain($user->name);
    $target .= format_date(REQUEST_TIME, 'short', '', NULL, $language_interface->langcode);

    // Test that the clear parameter cleans out non-existent tokens.
    $result = token_replace($source, array('node' => $node), array('langcode' => $language_interface->langcode, 'clear' => TRUE));
    $result = $this->assertEqual($target, $result, 'Valid tokens replaced while invalid tokens cleared out.');

    // Test without using the clear parameter (non-existent token untouched).
    $target .= '[user:name]';
    $target .= '[bogus:token]';
    $result = token_replace($source, array('node' => $node), array('langcode' => $language_interface->langcode));
    $this->assertEqual($target, $result, 'Valid tokens replaced while invalid tokens ignored.');

    // Check that the results of token_generate are sanitized properly. This does NOT
    // test the cleanliness of every token -- just that the $sanitize flag is being
    // passed properly through the call stack and being handled correctly by a 'known'
    // token, [node:title].
    $raw_tokens = array('title' => '[node:title]');
    $generated = token_generate('node', $raw_tokens, array('node' => $node));
    $this->assertEqual($generated['[node:title]'], check_plain($node->title), 'Token sanitized.');

    $generated = token_generate('node', $raw_tokens, array('node' => $node), array('sanitize' => FALSE));
    $this->assertEqual($generated['[node:title]'], $node->title, 'Unsanitized token generated properly.');

    // Test token replacement when the string contains no tokens.
    $this->assertEqual(token_replace('No tokens here.'), 'No tokens here.');
  }
}
