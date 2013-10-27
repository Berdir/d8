<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6TermSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests taxonomy term migration from D6 to D8.
 *
 * @group migrate
 */
class D6TermSourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Term';

  protected $migrationConfiguration = array(
    'id' => 'test',
    'highwaterProperty' => array('field' => 'test'),
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_term',
    ),
    'sourceIds' => array(
      'tid' => array(
        'alias' => 't',
      ),
    ),
    'destinationIds' => array(
      'tid' => array(),
    ),
  );

  protected $results = array(
    array(
      'tid' => 1,
      'vid' => 5,
      'name' => 'name value 1',
      'description' => 'description value 1',
      'weight' => 0,
      'parent' => 0,
    ),
    array(
      'tid' => 3,
      'vid' => 6,
      'name' => 'name value 3',
      'description' => 'description value 3',
      'weight' => 0,
      'parent' => 0,
    ),
    array(
      'tid' => 2,
      'vid' => 5,
      'name' => 'name value 2',
      'description' => 'description value 2',
      'weight' => 1,
      'parent' => 1,
    ),
    array(
      'tid' => 4,
      'vid' => 6,
      'name' => 'name value 4',
      'description' => 'description value 4',
      'weight' => 1,
      'parent' => 3,
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 taxonomy term source functionality',
      'description' => 'Tests D6 taxonomy term source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    foreach ($this->results as $k => $row) {
      $this->databaseContents['term_hierarchy'][$k]['tid'] = $row['tid'];
      $this->databaseContents['term_hierarchy'][$k]['parent'] = $row['parent'];
      unset($row['parent']);
      $this->databaseContents['term_data'][$k] = $row;
    }
    parent::setUp();
  }

}
