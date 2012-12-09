<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Bootstrap\HookBootExitTest.
 */

namespace Drupal\system\Tests\Bootstrap;

use Drupal\simpletest\WebTestBase;

/**
 * Tests hook_boot() and hook_exit().
 */
class HookBootExitTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system_test', 'dblog');

  public static function getInfo() {
    return array(
      'name' => 'Boot and exit hook invocation',
      'description' => 'Test that hook_boot() and hook_exit() are called correctly.',
      'group' => 'Bootstrap',
    );
  }

  /**
   * Tests calling of hook_boot() and the terminate event subscribers.
   */
  function testHookBootExit() {
    // Test with cache disabled. Boot and exit should always fire.
    $config = config('system.performance');
    $config->set('cache.page.enabled', 0);
    $config->save();

    $this->drupalGet('');
    $calls = 1;
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'hook_boot'))->fetchField(), $calls, 'hook_boot called with disabled cache.');
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'SystemTestCloseSubscriber'))->fetchField(), $calls, 'SystemTestCloseSubscriber called with disabled cache.');

    // Test with normal cache. Boot and exit should be called.
    $config->set('cache.page.enabled', 1);
    $config->save();
    $this->drupalGet('');
    $calls++;
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'hook_boot'))->fetchField(), $calls, 'hook_boot called with normal cache.');
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'SystemTestCloseSubscriber'))->fetchField(), $calls, 'SystemTestCloseSubscriber called with normal cache.');

    // Boot and exit should not fire since the page is cached.
    variable_set('page_cache_invoke_hooks', FALSE);
    $this->assertTrue(cache('page')->get(url('', array('absolute' => TRUE))), 'Page has been cached.');
    $this->drupalGet('');
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'hook_boot'))->fetchField(), $calls, 'hook_boot not called with aggressive cache and a cached page.');
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'SystemTestCloseSubscriber'))->fetchField(), $calls, 'SystemTestCloseSubscriber not called with aggressive cache and a cached page.');

    // Test with page cache cleared, boot and exit should be called.
    $this->assertTrue(db_delete('cache_page')->execute(), 'Page cache cleared.');
    $this->drupalGet('');
    $calls++;
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'hook_boot'))->fetchField(), $calls, 'hook_boot called with aggressive cache and no cached page.');
    $this->assertEqual(db_query('SELECT COUNT(*) FROM {watchdog} WHERE type = :type AND message = :message', array(':type' => 'system_test', ':message' => 'SystemTestCloseSubscriber'))->fetchField(), $calls, 'SystemTestCloseSubscriber called with aggressive cache and no cached page.');
  }
}
