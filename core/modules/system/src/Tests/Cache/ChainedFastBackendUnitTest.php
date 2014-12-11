<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Cache\ChainedFastBackendUnitTest.
 */

namespace Drupal\system\Tests\Cache;

use Drupal\Core\Cache\ChainedFastBackend;
use Drupal\Core\Cache\DatabaseBackend;
use Drupal\Core\Cache\PhpBackend;

/**
 * Unit test of the fast chained backend using the generic cache unit test base.
 *
 * @group Cache
 */
class ChainedFastBackendUnitTest extends GenericCacheBackendUnitTestBase {

  /**
   * Creates a new instance of ChainedFastBackend.
   *
   * @return
   *   A new ChainedFastBackend object.
   */
  protected function createCacheBackend($bin) {
    $consistent_backend = new DatabaseBackend($this->container->get('database'), $bin);
    $fast_backend = new PhpBackend($bin);
    return new ChainedFastBackend($consistent_backend, $fast_backend, $bin);
  }

  /**
   * Tests the expiration when the fast backend is invalidated.
   */
  public function testExpirationonWriteThrough() {
    $backend = $this->getCacheBackend();

    $backend->set('expired_data', 'foobar', REQUEST_TIME - 3);

    // Write a different cache entry to invalidate the fast backend and force
    // a write through from the consistent to the fast backend.
    $backend->set('other', 'data');

    $this->assertFalse($backend->get('expired_data'), 'Invalid item not returned.');
    $cached = $backend->get('expired_data', TRUE);
    $this->assert(is_object($cached), 'Backend returned an object for cache id expired_data.');
    $this->assertFalse($cached->valid, 'Item is marked as invalid.');
    $this->assertTrue($cached->created >= REQUEST_TIME && $cached->created <= round(microtime(TRUE), 3), 'Created time is correct.');
    $this->assertEqual($cached->expire, REQUEST_TIME - 3, 'Expire time is correct.');
  }

}
