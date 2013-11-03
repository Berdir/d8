<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\D6RoleSourceTest.
 */

namespace Drupal\migrate\Tests;

/**
 * Tests comment migration from D6 to D8.
 *
 * @group migrate
 */
class D6RoleSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate\Plugin\migrate\source\d6\Role';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The id of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    // This needs to be the identifier of the actual key: rid for comment, nid
    // for node and so on.
    'source' => array(
      'plugin' => 'drupal6_role',
    ),
    'sourceIds' => array(
      'rid' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'r',
      ),
    ),
    'destinationIds' => array(
      'rid' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  protected $results = array(
    array(
      'rid' => 1,
      'name' => 'anonymous user',
      'permissions' => array(
        array(
          'pid' => 1,
          'rid' => 1,
          'perm' => array(
            'access content',
          ),
          'tid' => 0,
        ),
      ),
    ),
    array(
      'rid' => 2,
      'name' => 'authenticated user',
      'permissions' => array(
        array(
          'pid' => 2,
          'rid' => 2,
          'perm' => array(
            'access comments',
            'access content',
            'post comments',
            'post comments without approval',
          ),
          'tid' => 0,
        ),
      ),
    ),
    array(
      'rid' => 3,
      'name' => 'administrator',
      'permissions' => array(
        array(
          'pid' => 3,
          'rid' => 3,
          'perm' => array(
            'access comments',
            'administer comments',
            'post comments',
            'post comments without approval',
            'access content',
            'administer content types',
            'administer nodes',
          ),
          'tid' => 0,
        ),
      ),
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 role source functionality',
      'description' => 'Tests D6 role source plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    foreach ($this->results as $k => $row) {
      foreach ($row['permissions'] as $perm) {
        $this->databaseContents['permissions'][$perm['pid']] = $perm;
        $this->databaseContents['permissions'][$perm['pid']]['perm'] = implode(',', $perm['perm']);
        $this->databaseContents['permissions'][$perm['pid']]['rid'] = $row['rid'];
      }
      unset($row['permissions']);
      $this->databaseContents['role'][$k] = $row;
    }
    parent::setUp();
  }

}
