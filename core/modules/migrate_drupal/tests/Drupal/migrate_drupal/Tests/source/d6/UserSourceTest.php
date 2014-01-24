<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\CommentSourceTest.
 */

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate_drupal
 */
class UserSourceTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate_drupal\Plugin\migrate\source\d6\User';

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

  protected $expectedResults = array(
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
      'data' => NULL,
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
      'data' => NULL,
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 user source functionality',
      'description' => 'Tests D6 user source plugin.',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    foreach ($this->expectedResults as $k => $row) {
      $this->databaseContents['users'][$k] = $row;
    }
    parent::setUp();
  }

}

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\User;

class TestUser extends User {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
