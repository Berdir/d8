<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateD6UserRoleTest.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;

class MigrateD6UserRoleTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate user roles to user.role.*.yml',
      'description'  => 'Upgrade user roles to user.role.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  function testUserRole() {
    $migration = entity_load('migration', 'd6_user_role');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserRole.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();

    $migrate_test_role = entity_load('user_role', 'migrate_test_role_1');
    $this->assertTrue(is_object($migrate_test_role), 'The migrated role was retrieved from the database.');
    $this->assertEqual($migrate_test_role->permissions, array(0 => 'migrate test role 1 test permission'));

    // @todo: write better asserts.
  }

}
