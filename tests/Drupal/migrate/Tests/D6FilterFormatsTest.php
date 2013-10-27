<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6FilterFormatsSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6FilterFormatsSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\FilterFormats';

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
      'roles' => ',1,2,',
      'cache' => 1,
      'filters' => array(
        array(
          'fid' => 1,
          'module' => 'filter',
          'delta' => 2,
          'weight' => 0,
        ),
        array(
          'fid' => 2,
          'module' => 'filter',
          'delta' => 0,
          'weight' => 1,
        ),
        array(
          'fid' => 3,
          'module' => 'filter',
          'delta' => 1,
          'weight' => 2,
        ),
      ),
    ),
    array(
      'format' => 2,
      'name' => 'Full HTML',
      'roles' => '',
      'cache' => 1,
      'filters' => array(
        array(
          'fid' => 5,
          'module' => 'filter',
          'delta' => 2,
          'weight' => 0,
        ),
        array(
          'fid' => 6,
          'module' => 'filter',
          'delta' => 1,
          'weight' => 1,
        ),
        array(
          'fid' => 7,
          'module' => 'filter',
          'delta' => 3,
          'weight' => 10,
        ),
      ),
    ),
    array(
      'format' => 4,
      'name' => 'Example Custom Format',
      'roles' => ',4,',
      'cache' => 1,
      'filters' => array(
        // This custom format uses a filter defined by a contrib module.
        array(
          'fid' => 8,
          'module' => 'markdown',
          'delta' => 1,
          'weight' => 10,
        ),
      ),
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 Filter Formats source functionality',
      'description' => 'Tests D6 filter_formats table source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    foreach ($this->results as $k => $row) {
      foreach ($row['filters'] as $filter) {
        $this->databaseContents['filters'][$filter['fid']] = $filter;
        $this->databaseContents['filters'][$filter['fid']]['format'] = $row['format'];
      }
      unset($row['filters']);
      $this->databaseContents['filter_formats'][$k] = $row;
    }
    parent::setUp();
  }
}
