<?php

/**
 * @file
 * Contains \Drupal\simpletest\Tests\SimpleTestWithLanguageTest.
 */

namespace Drupal\simpletest\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests SimpleTest's when language module is enabled.
 *
 * @group simpletest
 */
class SimpleTestWithLanguageTest extends WebTestBase {

  public static $modules = [
    'simpletest',
    'language',
    'locale',
  ];

  /**
   * Overrides DrupalWebTestCase::setUp().
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->drupalCreateUser(array('administer unit tests')));
  }

  /**
   * Ensures the tests run well with language module enabled.
   */
  protected function testSimpleTestWithLanguageModule() {
    $edit['tests[Drupal\language\Tests\LanguageListTest]'] = TRUE;
    $this->drupalPostForm('admin/config/development/testing', $edit, t('Run tests'));
    $this->assertRaw('0 exceptions');
    $this->assertRaw('0 fails');
    if (isset($this->xpath('//a[normalize-space(text())=:label]', array(':label' => 'the error page'))[0])) {
      $this->clickLink('the error page');
    }
  }

}
