<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Cache\DatabaseBackendUnitTest.
 */

namespace Drupal\system\Tests\Cache;

use Drupal\Core\Cache\DatabaseBackend;
use Drupal\Core\Database\Database;

/**
 * Tests DatabaseBackend using GenericCacheBackendUnitTestBase.
 */
class DatabaseBackendUnitTest extends GenericCacheBackendUnitTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Database backend',
      'description' => 'Unit test of the database backend using the generic cache unit test base.',
      'group' => 'Cache',
    );
  }

  protected function createCacheBackend($bin) {
    return new DatabaseBackend(Database::getConnection(), $bin);
  }

  public function setUpCacheBackend() {
    drupal_install_schema('system');
  }

  public function tearDownCacheBackend() {
    drupal_uninstall_schema('system');
  }
}
