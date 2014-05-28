<?php

/**
 * @file
 * Definition of Drupal\user\Tests\UserEntityCallbacksTest.
 */

namespace Drupal\user\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test user entity callbacks.
 */
class UserEntityCallbacksTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('user', 'user_name_test');

  /**
   * @var \Drupal\user\UserInterface
   */
  protected $account;

  /**
   * @var \Drupal\user\UserInterface
   */
  protected $anonymous;

  public static function getInfo() {
    return array(
      'name' => 'User entity callback tests',
      'description' => 'Tests specific parts of the user entity like the URI callback and the label callback.',
      'group' => 'User'
    );
  }

  function setUp() {
    parent::setUp();

    $this->account = $this->drupalCreateUser();
    $this->anonymous = entity_create('user', array('uid' => 0));
  }

  /**
   * Test label callback.
   */
  function testLabelCallback() {
    $this->assertEqual($this->account->label(), $this->account->getUsername(), 'The username should be used as label');

    // Setup a random anonymous name to be sure the name is used.
    $name = $this->randomName();
    \Drupal::config('user.settings')->set('anonymous', $name)->save();
    $this->assertEqual($this->anonymous->label(), $name, 'The variable anonymous should be used for name of uid 0');
    $this->assertEqual($this->anonymous->getUserName(), '', 'The raw anonymous user name should be empty string');

    // Set to test the altered username.
    \Drupal::state()->set('user_name_test_altered_name', 'altered');

    $this->assertEqual($this->account->getDisplayName(), $this->account->name->value . 'altered', 'The user display name should be altered.');
    $this->assertEqual($this->account->getUsername(), $this->account->name->value, 'The user name should not be altered.');
  }

  /**
   * Test URI callback.
   */
  function testUriCallback() {
    $this->assertEqual('user/' . $this->account->id(), $this->account->getSystemPath(), 'Correct user URI.');
  }
}
