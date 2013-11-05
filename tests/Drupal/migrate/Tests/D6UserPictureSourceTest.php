<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6UserPictureSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests user picture migration from D6 to D8.
 *
 * @group migrate
 */
class D6UserPictureSourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\UserPicture';

  protected $migrationConfiguration = array(
    'id' => 'test_user_picture',
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_user_picture',
    ),
    'sourceIds' => array(
      'uid' => array(
        'alias' => 'u',
      ),
    ),
    'destinationIds' => array(
      'user_picture' => array(
      ),
    ),
  );

  protected $results = array(
    array(
      'uid' => 1,
      'access' => 1382835435,
      'picture' => 'sites/default/files/pictures/picture-1.jpg',
    ),
    array(
      'uid' => 2,
      'access' => 1382835436,
      'picture' => 'sites/default/files/pictures/picture-2.jpg',
    ),
  );

  public function setUp() {
    $this->databaseContents['users'] = $this->results;
    parent::setUp();
  }

  public static function getInfo() {
    return array(
      'name' => 'D6 user picture source functionality',
      'description' => 'Tests D6 user picture source plugin.',
      'group' => 'Migrate',
    );
  }

}
