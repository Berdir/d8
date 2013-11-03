<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6ContactCategorySourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests contact category migration from D6 to D8.
 *
 * @group migrate
 */
class D6ContactCategorySourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\ContactCategory';

  protected $migrationConfiguration = array(
    'id' => 'test',
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_contact_category',
    ),
    'sourceIds' => array(
      'cid' => array(
        'alias' => 'c',
      ),
    ),
    'destinationIds' => array(
      'cid' => array(),
    ),
  );

  protected $results = array(
    array(
      'cid' => 1,
      'category' => 'contact category value 1',
      'recipients' => 'admin@example.com,user@example.com',
      'reply' => 'auto reply value 1',
      'weight' => 0,
      'selected' => 0,
    ),
    array(
      'cid' => 2,
      'category' => 'contact category value 2',
      'recipients' => 'admin@example.com,user@example.com',
      'reply' => 'auto reply value 2',
      'weight' => 0,
      'selected' => 0,
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 contact category source functionality',
      'description' => 'Tests D6 contact category source plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    foreach ($this->results as $k => $row) {
      $this->databaseContents['contact'][$k] = $row;
    }
    parent::setUp();
  }

}
