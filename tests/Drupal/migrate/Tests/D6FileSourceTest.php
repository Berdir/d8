<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6FileSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests file migration from D6 to D8.
 *
 * @group migrate
 */
class D6FileSourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\File';
  const BASE_TABLE = 'file';
  const BASE_ALIAS = 'f';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The ID of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_file',
    ),
    'sourceIds' => array(
      'fid' => array(
        'alias' => 'f',
      ),
    ),
    'destinationIds' => array(
      'fid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  protected $expectedResults = array(
    array(
      'fid' => 1,
      'uid' => 1,
      'filename' => 'migrate-test-file-1.pdf',
      'filepath' => 'sites/default/files/migrate-test-file-1.pdf',
      'filemime' => 'application/pdf',
      'filesize' => 890404,
      'status' => 1,
      'timestamp' => 1382255613,
    ),
    array(
      'fid' => 2,
      'uid' => 1,
      'filename' => 'migrate-test-file-2.pdf',
      'filepath' => 'sites/default/files/migrate-test-file-2.pdf',
      'filemime' => 'application/pdf',
      'filesize' => 204124,
      'status' => 1,
      'timestamp' => 1382255662,
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 file source functionality',
      'description' => 'Tests D6 file source plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->databaseContents['files'] = $this->expectedResults;
    parent::setUp();
  }

}

namespace Drupal\migrate\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Plugin\migrate\source\d6\File;

class TestFile extends File {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
