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

  /**
   * Creates a new instance of DatabaseBackend.
   *
   * @return
   *   A new DatabaseBackend object.
   */
  protected function createCacheBackend($bin) {
    return new DatabaseBackend($bin, Database::getConnection());
  }

  /**
   * Installs system schema.
   */
  public function setUpCacheBackend() {
    drupal_install_schema('system');
  }

  /**
   * Uninstalls system schema.
   */
  public function tearDownCacheBackend() {
    drupal_uninstall_schema('system');
  }
}
