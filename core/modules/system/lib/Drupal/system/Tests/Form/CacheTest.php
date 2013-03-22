<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Form\CacheTest.
 */

namespace Drupal\system\Tests\Form;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Test form caching.
 */
class CacheTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'user');

  public static function getInfo() {
    return array(
      'name' => 'Form cache tests',
      'description' => 'Tests form_set_cache() and form_get_cache()',
      'group' => 'Form API',
    );
  }

  public function setUp() {
    parent::setUp();
    $this->installSchema('system', array('key_value_expire'));

    // @todo The global user is the one from the parent site, so the uid needs to
    //   be restored. the base class should provide a mocked user.
    $this->original_uid = $GLOBALS['user']->uid;

    $this->form_build_id = $this->randomName();
    $this->form = array(
      '#property' => $this->randomName(),
    );
    $this->form_state = form_state_defaults();
    $this->form_state['example'] = $this->randomName();
  }

  /**
   * Tests the form cache with a logged in user.
   */
  function testCacheToken() {
    $GLOBALS['user']->uid = 1;
    form_set_cache($this->form_build_id, $this->form, $this->form_state);

    $cached_form_state = form_state_defaults();
    $cached_form = form_get_cache($this->form_build_id, $cached_form_state);
    $this->assertEqual($this->form['#property'], $cached_form['#property']);
    $this->assertTrue(!empty($cached_form['#cache_token']), 'Form has a cache token');
    $this->assertEqual($this->form_state['example'], $cached_form_state['example']);

    // Test that the form cache isn't loaded when the session/token has changed.
    // Change the private key which has the same effect as changing the
    //session id in a unit test affects the parent site batch.
    state()->set('system.private_key', 'invalid');
    $cached_form_state = form_state_defaults();
    $cached_form = form_get_cache($this->form_build_id, $cached_form_state);
    $this->assertFalse($cached_form, 'No form returned from cache');
    $this->assertTrue(empty($cached_form_state['example']));

    // Test that loading the cache with a different form_id fails.
    $wrong_form_build_id = $this->randomName(9);
    $cached_form_state = form_state_defaults();
    $this->assertFalse(form_get_cache($wrong_form_build_id, $cached_form));
    $this->assertTrue(empty($cached_form_state['example']), 'Cached form state was not loaded');
  }

  /**
   * Tests the form cache without a logged in user.
   */
  function testNoCacheToken() {
    $GLOBALS['user']->uid = 0;

    $this->form_state['example'] = $this->randomName();
    form_set_cache($this->form_build_id, $this->form, $this->form_state);

    $cached_form_state = form_state_defaults();
    $cached_form = form_get_cache($this->form_build_id, $cached_form_state);
    $this->assertEqual($this->form['#property'], $cached_form['#property']);
    $this->assertTrue(empty($cached_form['#cache_token']), 'Form has no cache token');
    $this->assertEqual($this->form_state['example'], $cached_form_state['example']);
  }

  function tearDown() {
    $GLOBALS['user']->uid = $this->original_uid;
    parent::tearDown();
  }

}
