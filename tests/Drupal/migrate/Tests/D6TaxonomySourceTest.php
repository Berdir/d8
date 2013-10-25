<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6TaxonomySourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests taxonomy migration from D6 to D8.
 *
 * @group migrate
 */
class D6TaxonomySourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Taxonomy';

  const PLUGIN_ID = 'drupal6_taxonomy';

  const BASE_TABLE = 'term_data';

  const BASE_ALIAS = 'td';

  protected $migrationConfiguration = array(
    'id' => 'test',
    'highwaterProperty' => array('field' => 'test'),
    'idlist' => array(),
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
      'tid' => 2,
      'vid' => 6,
      'name' => 'name value 2',
      'description' => 'description value 2',
      'weight' => 1,
      'parent' => 1,
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 taxonomy source functionality',
      'description' => 'Tests D6 taxonomy source plugin.',
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
