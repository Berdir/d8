<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Cache\BackendChainUnitTest.
 */

namespace Drupal\system\Tests\Cache;

use Drupal\Core\Cache\BackendChain;
use Drupal\Core\Cache\MemoryBackend;

/**
 * Unit test of the backend chain using the generic cache unit test base.
 *
 * @group Cache
 */
class BackendChainUnitTest extends GenericCacheBackendUnitTestBase {

  protected function createCacheBackend($bin) {
    $chain = new BackendChain($bin);

    // We need to create some various backends in the chain.
    $chain
      ->appendBackend(new MemoryBackend('foo'))
      ->prependBackend(new MemoryBackend('bar'))
      ->appendBackend(new MemoryBackend('baz'));

    return $chain;
  }

  /**
   * Tests that the expiration works correctly
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
