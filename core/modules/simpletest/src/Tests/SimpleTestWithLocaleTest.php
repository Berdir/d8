<?php

/**
 * @file
 * Contains \Drupal\simpletest\Tests\SimpleTestWithLocaleTest.
 */

namespace Drupal\simpletest\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests SimpleTest's when locale module is enabled.
 *
 * @group simpletest
 */
class SimpleTestWithLocaleTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'simpletest',
    'language',
    'locale',
  ];

  /**
   * Ensures the tests run well with locale enabled.
   */
  protected function testSimpleTestWithLanguageModule() {
    $this->drupalLogin($this->drupalCreateUser(array('administer unit tests')));
    $edit['tests[Drupal\simpletest\Tests\SimpleTestNoLocaleTest]'] = TRUE;
    $this->drupalPostForm('admin/config/development/testing', $edit, t('Run tests'));
    $this->assertRaw('0 exceptions');
    $this->assertRaw('0 fails');
    $this->assertText('successfully logged in.');
    if (isset($this->xpath('//a[normalize-space(text())=:label]', array(':label' => 'the error page'))[0])) {
      $this->clickLink('the error page');
    }
  }

}
