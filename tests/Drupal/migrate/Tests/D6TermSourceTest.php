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
      'parents' => array(0),
    ),
    array(
      'tid' => 2,
      'vid' => 6,
      'name' => 'name value 2',
      'description' => 'description value 2',
      'weight' => 0,
      'parents' => array(0),
    ),
    array(
      'tid' => 3,
      'vid' => 6,
      'name' => 'name value 3',
      'description' => 'description value 3',
      'weight' => 0,
      'parents' => array(0),
    ),
    array(
      'tid' => 4,
      'vid' => 5,
      'name' => 'name value 4',
      'description' => 'description value 4',
      'weight' => 1,
      'parents' => array(1),
    ),
    array(
      'tid' => 5,
      'vid' => 6,
      'name' => 'name value 5',
      'description' => 'description value 5',
      'weight' => 1,
      'parents' => array(2),
    ),
    array(
      'tid' => 6,
      'vid' => 6,
      'name' => 'name value 6',
      'description' => 'description value 6',
      'weight' => 0,
      'parents' => array(3, 2),
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 taxonomy term source functionality',
      'description' => 'Tests D6 taxonomy term source plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    foreach ($this->results as $k => $row) {
      foreach ($row['parents'] as $parent) {
        $this->databaseContents['term_hierarchy'][] = array(
          'tid' => $row['tid'],
          'parent' => $parent,
        );
      }
      unset($row['parents']);
      $this->databaseContents['term_data'][$k] = $row;
    }
    parent::setUp();
  }

}
