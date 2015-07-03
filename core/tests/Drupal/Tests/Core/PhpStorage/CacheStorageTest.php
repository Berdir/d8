<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\PhpStorage\CacheStorageTest.
 */

namespace Drupal\Tests\Core\PhpStorage;

use Drupal\Core\Cache\MemoryBackend;
use Drupal\Core\PhpStorage\CacheStorage;
use Drupal\Core\Site\Settings;
use Drupal\Tests\Component\PhpStorage\PhpStorageTestBase;

/**
 * @coversDefaultClass \Drupal\Core\PhpStorage\CacheStorage
 * @group Drupal
 * @group PhpStorage
 */
class CacheStorageTest extends PhpStorageTestBase {

  /**
   * Tests basic load/save/delete operations.
   *
   * @covers ::load
   * @covers ::save
   * @covers ::exists
   * @covers ::delete
   */
  public function testCRUD() {
    $secret = $this->randomMachineName();
    $storage = new CacheStorage([
      'secret' => $secret,
      'cache_backend_factory' => function (array $configuration) { return new MemoryBackend($configuration['bin']);},
      'bin' => 'test'
    ]);
    $this->assertCRUD($storage);
  }

}
