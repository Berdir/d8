<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Render\RenderCacheTest.
 */

namespace Drupal\system\Tests\Render;

use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the caching of render items via functional tests.
 *
 * @group Render
 */
class RenderCacheTest extends WebTestBase {

  /**
   * Tests the caching of render items.
   */
  public function testDrupalRenderCache() {
    // Force a request via GET.
    $request_method = \Drupal::request()->getMethod();
    \Drupal::request()->setMethod('GET');

    // Test that user 1 does not share the cache with other users who have the
    // same roles, even when using a role-based cache context.
    $user1 = User::load(1);
    $first_authenticated_user = $this->drupalCreateUser();
    $second_authenticated_user = $this->drupalCreateUser();
    $this->assertEqual($user1->getRoles(), $first_authenticated_user->getRoles(), 'User 1 has the same roles as an authenticated user.');
    // Impersonate user 1 and render content that only user 1 should have
    // permission to see.
    \Drupal::service('account_switcher')->switchTo($user1);
    $test_element = array(
      '#cache' => array(
        'keys' => array('test'),
        'contexts' => array('user.roles'),
      ),
    );
    $element = $test_element;
    $element['#markup'] = 'content for user 1';
    $output = \Drupal::service('renderer')->render($element);
    $this->assertEqual($output, 'content for user 1');
    // Verify the cache is working by rendering the same element but with
    // different markup passed in; the result should be the same.
    $element = $test_element;
    $element['#markup'] = 'should not be used';
    $output = \Drupal::service('renderer')->render($element);
    $this->assertEqual($output, 'content for user 1');
    \Drupal::service('account_switcher')->switchBack();
    // Verify that the first authenticated user does not see the same content
    // as user 1.
    \Drupal::service('account_switcher')->switchTo($first_authenticated_user);
    $element = $test_element;
    $element['#markup'] = 'content for authenticated users';
    $output = \Drupal::service('renderer')->render($element);
    $this->assertEqual($output, 'content for authenticated users');
    \Drupal::service('account_switcher')->switchBack();
    // Verify that the second authenticated user shares the cache with the
    // first authenticated user.
    \Drupal::service('account_switcher')->switchTo($second_authenticated_user);
    $element = $test_element;
    $element['#markup'] = 'should not be used';
    $output = \Drupal::service('renderer')->render($element);
    $this->assertEqual($output, 'content for authenticated users');
    \Drupal::service('account_switcher')->switchBack();

    // Restore the previous request method.
    \Drupal::request()->setMethod($request_method);
  }

}
