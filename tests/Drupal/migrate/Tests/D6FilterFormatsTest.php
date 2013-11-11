<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6FilterFormatsTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6FilterFormatsTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\D6FilterFormats';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    'id' => 'test',
    'highwaterProperty' => array('field' => 'test'),
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_filter_formats',
    ),
    'sourceIds' => array(
      'format' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'f',
      ),
    ),
    'destinationIds' => array(
    ),
  );

  protected $results = array(
    array(
      'format' => 1,
      'name' => 'Filtered HTML',
      'roles' => array(1, 2),
      'cache' => 1,
      'filters' => array(
        array(
          'module' => 'filter',
          'delta' => 2,
          'weight' => 0,
        ),
        array(
          'module' => 'filter',
          'delta' => 0,
          'weight' => 1,
        ),
        array(
          'module' => 'filter',
          'delta' => 1,
          'weight' => 2,
        ),
      ),
    ),
    array(
      'format' => 2,
      'name' => 'Full HTML',
      'roles' => array(),
      'cache' => 1,
      'filters' => array(
        array(
          'module' => 'filter',
          'delta' => 2,
          'weight' => 0,
        ),
        array(
          'module' => 'filter',
          'delta' => 1,
          'weight' => 1,
        ),
        array(
          'module' => 'filter',
          'delta' => 3,
          'weight' => 10,
        ),
      ),
    ),
    array(
      'format' => 4,
      'name' => 'Example Custom Format',
      'roles' => array(4),
      'cache' => 1,
      'filters' => array(
        // This custom format uses a filter defined by a contrib module.
        array(
          'module' => 'markdown',
          'delta' => 1,
          'weight' => 10,
        ),
      ),
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 Filter Formats source functionality',
      'description' => 'Tests D6 filter_formats table source plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $fid = 1;
    foreach ($this->results as $k => $row) {
      $row['roles'] = ',' . implode(',', $row['roles']) . ',';
      foreach ($row['filters'] as $filter) {
        $this->databaseContents['filters'][$fid] = $filter;
        $this->databaseContents['filters'][$fid]['format'] = $row['format'];
        $this->databaseContents['filters'][$fid]['fid'] = $fid;
        $fid++;
      }
      unset($row['filters']);
      $this->databaseContents['filter_formats'][$k] = $row;
    }
    parent::setUp();
  }
}

namespace Drupal\migrate\Tests\source;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Plugin\migrate\source\D6FilterFormats;

class TestD6FilterFormats extends D6FilterFormats {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
