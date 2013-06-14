<?php

/**
 * @file
 * Contains Drupal\Tests\Core\Cache\CacheCollectorTest.
 */

namespace Drupal\Tests\Core\Cache;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Tests the cache CacheCollector.
 *
 * @group Cache
 */
class CacheCollectorTest extends UnitTestCase {

  /**
   * The cache backend that should be used.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * The lock backend that should be used.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $lock;

  /**
   * The cache id used for the test.
   *
   * @var string
   */
  protected $cid;

  /**
   * Cache collector implementation to test.
   *
   * @var \Drupal\Tests\Core\Cache\CacheCollectorHelper
   */
  protected $collector;

  public static function getInfo() {
    return array(
      'name' => 'CacheCollector test',
      'description' => 'Tests the cache collector base implementaion',
      'group' => 'Cache',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $this->lock = $this->getMock('Drupal\Core\Lock\LockBackendInterface');
    $this->cid = $this->randomName();
  }


  /**
   * Tests the resolve cache miss function.
   */
  public function testResolveCacheMiss() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();
    $this->collector->setCacheMissData($key, $value);

    $this->assertEquals($value, $this->collector->get($key));
  }

  /**
   * Tests setting and getting values when the cache is empty.
   */
  public function testSetAndGet() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();

    $this->assertNull($this->collector->get($key));

    $this->collector->set($key, $value);
    $this->assertTrue($this->collector->has($key));
    $this->assertEquals($value, $this->collector->get($key));
  }


  /**
   * Makes sure that NULL is a valid value and is collected.
   */
  public function testSetAndGetNull() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = NULL;

    $this->constructCollector();

    $this->cache->expects($this->once())
      ->method('invalidate')
      ->with($this->cid);
    $this->collector->set($key, $value);
    $this->assertTrue($this->collector->has($key));
    $this->assertEquals($value, $this->collector->get($key));
  }

  /**
   * Tests returning value from the collected cache.
   */
  public function testGetFromCache() {
    $key = $this->randomName();
    $value = $this->randomName();

    $cache = (object) array(
      'data' => array($key => $value),
      'created' => REQUEST_TIME,
    );
    $this->cache->expects($this->once())
      ->method('get')
      ->with($this->cid)
      ->will($this->returnValue($cache));
    $this->constructCollector();
    $this->assertTrue($this->collector->has($key));
    $this->assertEquals($value, $this->collector->get($key));
    $this->assertEquals(0, $this->collector->getCacheMisses());
  }

  /**
   * Tests setting and deleting values.
   */
  public function testDelete() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();

    $this->assertNull($this->collector->get($key));

    $this->collector->set($key, $value);
    $this->assertTrue($this->collector->has($key));
    $this->assertEquals($value, $this->collector->get($key));

    $this->cache->expects($this->once())
      ->method('invalidate')
      ->with($this->cid);
    $this->collector->delete($key);
    $this->assertFalse($this->collector->has($key));
    $this->assertEquals(NULL, $this->collector->get($key));
  }

  /**
   * Tests updating the cache when no changes were made.
   */
  public function testUpdateCacheNoChanges() {
    $this->constructCollector();

    $this->lock->expects($this->never())
      ->method('acquire');
    $this->cache->expects($this->never())
      ->method('set');

    $this->collector->destruct();
  }

  /**
   * Tests updating the cache after a set.
   */
  public function testUpdateCache() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();

    $this->collector->setCacheMissData($key, $value);
    $this->collector->get($key);

    // Set up mock objects for the expected calls, first a lock acquire, then
    // cache get to look for conflicting cache entries, then a cache set and
    // finally the lock is released again.
    $this->lock->expects($this->once())
      ->method('acquire')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector')
      ->will($this->returnValue(TRUE));
    $this->cache->expects($this->once())
      ->method('get')
      ->with($this->cid, FALSE);
    $this->cache->expects($this->once())
      ->method('set')
      ->with($this->cid, array($key => $value), CacheBackendInterface::CACHE_PERMANENT, array());
    $this->lock->expects($this->once())
      ->method('release')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector');

    $this->collector->destruct();
  }


  /**
   * Tests updating the cache when the lock acquire fails.
   */
  public function testUpdateCacheLockFail() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();

    $this->collector->setCacheMissData($key, $value);
    $this->collector->get($key);

    // The lock acquire returns false, so the method should abort.
    $this->lock->expects($this->once())
      ->method('acquire')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector')
      ->will($this->returnValue(FALSE));
    $this->cache->expects($this->never())
      ->method('set');

    $this->collector->destruct();
  }

  /**
   * Tests updating the cache when there is a
   */
  public function testUpdateCacheInvalidatedConflict() {
    $key = $this->randomName();
    $value = $this->randomName();

    $cache = (object) array(
      'data' => array($key => $value),
      'created' => REQUEST_TIME,
    );
    $this->cache->expects($this->at(0))
      ->method('get')
      ->with($this->cid)
      ->will($this->returnValue($cache));
    $this->constructCollector();

    $this->cache->expects($this->at(0))
      ->method('invalidate')
      ->with($this->cid);
    $this->collector->set($key, 'new value');

    // Set up mock objects for the expected calls, first a lock acquire, then
    // cache get to look for conflicting cache entries, which does find
    // and then it deletes the cache and aborts.
    $this->lock->expects($this->once())
      ->method('acquire')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector')
      ->will($this->returnValue(TRUE));
    $cache = (object) array(
      'data' => array($key => $value),
      'created' => REQUEST_TIME + 1,
    );
    $this->cache->expects($this->at(0))
      ->method('get')
      ->with($this->cid)
      ->will($this->returnValue($cache));
    $this->cache->expects($this->once())
      ->method('delete')
      ->with($this->cid);
    $this->lock->expects($this->once())
      ->method('release')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector');

    $this->collector->destruct();
  }

  /**
   * Tests updating the cache when a different request
   */
  public function testUpdateCacheMerge() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();

    $this->collector->setCacheMissData($key, $value);
    $this->collector->get($key);

    // Set up mock objects for the expected calls, first a lock acquire, then
    // cache get to look for existing cache entries, which does find
    // and then it merges them.
    $this->lock->expects($this->once())
      ->method('acquire')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector')
      ->will($this->returnValue(TRUE));
    $cache = (object) array(
      'data' => array('other key' => 'other value'),
      'created' => REQUEST_TIME + 1,
    );
    $this->cache->expects($this->at(0))
      ->method('get')
      ->with($this->cid)
      ->will($this->returnValue($cache));
    $this->cache->expects($this->once())
      ->method('set')
      ->with($this->cid, array('other key' => 'other value', $key => $value), CacheBackendInterface::CACHE_PERMANENT, array());
    $this->lock->expects($this->once())
      ->method('release')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector');

    $this->collector->destruct();
  }

  /**
   * Tests updating the cache after a delete.
   */
  public function testUpdateCacheDelete() {
    $key = $this->randomName();
    $value = $this->randomName();

    $cache = (object) array(
      'data' => array($key => $value),
      'created' => REQUEST_TIME,
    );
    $this->cache->expects($this->at(0))
      ->method('get')
      ->with($this->cid)
      ->will($this->returnValue($cache));
    $this->constructCollector();

    $this->collector->delete($key);

    // Set up mock objects for the expected calls, first a lock acquire, then
    // cache get to look for conflicting cache entries, then a cache set and
    // finally the lock is released again.
    $this->lock->expects($this->once())
      ->method('acquire')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector')
      ->will($this->returnValue(TRUE));
    // The second argument is set to TRUE because we triggered a cache
    // invalidation.
    $this->cache->expects($this->at(0))
      ->method('get')
      ->with($this->cid, TRUE);
    $this->cache->expects($this->once())
      ->method('set')
      ->with($this->cid, array(), CacheBackendInterface::CACHE_PERMANENT, array());
    $this->lock->expects($this->once())
      ->method('release')
      ->with($this->cid . ':Drupal\Core\Cache\CacheCollector');

    $this->collector->destruct();
  }

  /**
   * Tests a reset of the cache collector.
   */
  public function testUpdateCacheReset() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();

    // Set the data and request it.
    $this->collector->setCacheMissData($key, $value);
    $this->assertEquals($value, $this->collector->get($key));
    $this->assertEquals($value, $this->collector->get($key));

    // Should have been added to the storage and only be requested once.
    $this->assertEquals(1, $this->collector->getCacheMisses());

    // Reset the collected cache, should call it again.
    $this->collector->reset();
    $this->assertEquals($value, $this->collector->get($key));
    $this->assertEquals(2, $this->collector->getCacheMisses());
  }

  /**
   * Tests a clear of the cache collector.
   */
  public function testUpdateCacheClear() {
    $this->constructCollector();
    $key = $this->randomName();
    $value = $this->randomName();

    // Set the data and request it.
    $this->collector->setCacheMissData($key, $value);
    $this->assertEquals($value, $this->collector->get($key));
    $this->assertEquals($value, $this->collector->get($key));

    // Should have been added to the storage and only be requested once.
    $this->assertEquals(1, $this->collector->getCacheMisses());

    // Clear the collected cache, should call it again.
    $this->cache->expects($this->once())
      ->method('delete')
      ->with($this->cid);
    $this->collector->clear();
    $this->assertEquals($value, $this->collector->get($key));
    $this->assertEquals(2, $this->collector->getCacheMisses());
  }

  /**
   * Constructs the collector.
   *
   * Separate methods so that the cache mock can be set up before calling the
   * constructor.
   */
  protected function constructCollector() {
    $this->collector = new CacheCollectorHelper($this->cid, $this->cache, $this->lock);
  }
}
