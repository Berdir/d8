<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Cache\SmartCacheIntegrationTest.
 */

namespace Drupal\system\Tests\Cache;

use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Url;
use Drupal\simpletest\WebTestBase;
use Drupal\system\Tests\Cache\AssertPageCacheContextsAndTagsTrait;

/**
 * Enables the SmartCache and tests it in various scenarios.
 *
 * @group Cache
 *
 * @see \Drupal\Core\EventSubscriber\SmartCacheSubscriber
 * @see \Drupal\Core\Render\MainContent\SmartCacheHtmlRenderer
 */
class SmartCacheIntegrationTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  protected $dumpHeaders = TRUE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['smart_cache_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Uninstall the page_cache module; we want to test the SmartCache alone.
    \Drupal::service('module_installer')->uninstall(['page_cache']);
  }

  /**
   * Tests that SmartCache works correctly, and verifies the edge cases.
   */
  function testSmartCache() {
    // Controllers returning response objects are ignored by SmartCache.
    $url = Url::fromUri('route:smart_cache_test.response');
    $this->drupalGet($url);
    $this->assertFalse($this->drupalGetHeader('X-Drupal-SmartCache'), 'Response object returned: SmartCache is ignoring.');

    // Controllers returning render arrays, rendered as HTML responses, are
    // handled by SmartCache.
    $url = Url::fromUri('route:smart_cache_test.html');
    $this->drupalGet($url);
    $this->assertEqual('MISS', $this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as HTML response: SmartCache is active, SmartCache MISS.');
    $this->assertSmartCache($url, [], []);
    $this->drupalGet($url);
    $this->assertEqual('HIT', $this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as HTML response: SmartCache is active, SmartCache HIT.');

    // The above is the simple case, where the render array returned by the
    // response contains no cache contexts. So let's now test a route/controller
    // that *does* vary by a cache context whose value we can easily control: it
    // varies by the 'animal' query argument.
    foreach (['llama', 'piggy', 'unicorn', 'kitten'] as $animal) {
      $url = Url::fromUri('route:smart_cache_test.html.with_cache_contexts', ['query' => ['animal' => $animal]]);
      $this->drupalGet($url);
      $this->assertEqual('MISS', $this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as HTML response: SmartCache is active, SmartCache MISS.');
      $this->assertSmartCache($url, ['url.query_args:animal'], [$animal]);
      $this->drupalGet($url);
      $this->assertEqual('HIT', $this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as HTML response: SmartCache is active, SmartCache HIT.');

      // Finally, let's also verify that the 'smart_cache_test.html' route
      // continued to see cache hits if we specify a query argument, because it
      // *should* ignore it and continue to provide SmartCache hits.
      $url = Url::fromUri('route:smart_cache_test.html', ['query' => ['animal' => 'piglet']]);
      $this->drupalGet($url);
      $this->assertEqual('HIT', $this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as HTML response: SmartCache is active, SmartCache HIT.');
    }

    // Controllers returning render arrays, rendered as anything except a HTML
    // response, are ignored by SmartCache.
    $this->drupalGet('smart-cache-test/html', array('query' => array(MainContentViewSubscriber::WRAPPER_FORMAT => 'drupal_ajax')));
    $this->assertFalse($this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as AJAX response: SmartCache is ignoring.');
    $this->drupalGet('smart-cache-test/html', array('query' => array(MainContentViewSubscriber::WRAPPER_FORMAT => 'drupal_dialog')));
    $this->assertFalse($this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as dialog response: SmartCache is ignoring.');
    $this->drupalGet('smart-cache-test/html', array('query' => array(MainContentViewSubscriber::WRAPPER_FORMAT => 'drupal_modal')));
    $this->assertFalse($this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as modal response: SmartCache is ignoring.');

    // Admin routes are ignored by SmartCache.
    $this->drupalGet('smart-cache-test/html/admin');
    $this->assertFalse($this->drupalGetHeader('X-Drupal-SmartCache'), 'Response returned, rendered as HTML response, admin route: SmartCache is ignoring');
    $this->drupalGet('smart-cache-test/response/admin');
    $this->assertFalse($this->drupalGetHeader('X-Drupal-SmartCache'), 'Response returned, admin route: SmartCache is ignoring');

    // Max-age = 0 responses are ignored by SmartCache.
    $this->drupalGet('smart-cache-test/html/uncacheable');
    $this->assertEqual('UNCACHEABLE', $this->drupalGetHeader('X-Drupal-SmartCache'), 'Render array returned, rendered as HTML response, but uncacheable: SmartCache is running, but not caching.');
  }

  /**
   * Asserts SmartCache cache items.
   *
   * @param \Drupal\Core\Url $url
   *   The URL to test.
   * @param string[] $expected_cache_contexts
   *   The expected cache contexts for the given URL.
   * @param string[] $cid_parts_for_cache_contexts
   *   The CID parts corresponding to the values in $expected_cache_contexts.
   */
  protected function assertSmartCache(Url $url, array $expected_cache_contexts, array $cid_parts_for_cache_contexts) {
    // Assert SmartCache contexts item.
    $cid_parts = ['smartcache', 'contexts', $url->getRouteName() . hash('sha256', serialize($url->getRouteParameters()))];
    $cid = implode(':', $cid_parts);
    $cache_item = \Drupal::cache('smart_cache_contexts')->get($cid);
    $this->assertEqual($expected_cache_contexts, array_values(array_diff($cache_item->data, ['route'])));

    // Assert SmartCache html render array item.
    $cid_parts = ['smartcache', 'html_render_array', $url->getRouteName() . hash('sha256', serialize($url->getRouteParameters()))];
    $cid_parts = array_merge($cid_parts, $cid_parts_for_cache_contexts);
    $cid = implode(':', $cid_parts);
    $cache_item = \Drupal::cache('smart_cache_html')->get($cid);
    $this->assertTrue($cache_item->data['#type'] === 'html');
  }

}
