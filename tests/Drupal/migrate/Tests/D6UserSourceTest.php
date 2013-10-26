<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6CommentSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6UserSourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\User';

  const BASE_TABLE = 'users';

  const BASE_ALIAS = 'u';

  protected $migrationConfiguration = array(
    'id' => 'test',
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_user',
    ),
    'sourceIds' => array(
      'uid' => array(
        // This is where the field schema would go.
        'alias' => 'u',
      ),
    ),
    'destinationIds' => array(
      'uid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  protected $results = array(
    array(
      'uid' => 1,
      'name' => 'admin',
      // @todo d6 hash?
      'pass' => '1234',
      'mail' => 'admin@example.com',
      'mode' => 0,
      'sort' => 0,
      'threshold' => 0,
      'theme' => '',
      'signature' => '',
      'signature_format' => 0,
      'created' => 1279402616,
      'access' => 1322981278,
      'login' => 1322699994,
      'status' => 0,
      'timezone' => 'America/Lima',
      'language' => 'en',
      // @todo Add the file when needed.
      'picture' => 'sites/default/files/pictures/picture-1.jpg',
      'init' => 'admin@example.com',
      'data' => null,
    ),
    array(
      'uid' => 4,
      'name' => 'alice',
      // @todo d6 hash?
      'pass' => '1234',
      'mail' => 'alice@example.com',
      'mode' => 0,
      'sort' => 0,
      'threshold' => 0,
      'theme' => '',
      'signature' => '',
      'signature_format' => 0,
      'created' => 1322981368,
      'access' => 1322982419,
      'login' => 132298140,
      'status' => 0,
      'timezone' => 'America/Lima',
      'language' => 'en',
      'picture' => '',
      'init' => 'alice@example.com',
      'data' => null,
    ),
  );

  public static function getInfo() {
    return array(
      'name' => 'D6 user source functionality',
      'description' => 'Tests D6 user source plugin.',
      'group' => 'Migrate',
    );
  }

  public function setUp() {
    foreach ($this->results as $k => $row) {
      $this->databaseContents['users'][$k] = $row;
    }
    parent::setUp();
  }

}
