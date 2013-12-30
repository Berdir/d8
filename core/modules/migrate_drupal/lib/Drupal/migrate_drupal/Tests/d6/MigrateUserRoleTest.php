<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateD6UserRoleTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUserRoleTest extends MigrateDrupalTestBase {

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
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_user_role');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserRole.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();

    $migrate_test_role_1 = entity_load('user_role', 'migrate_test_role_1');
    $this->assertTrue(is_object($migrate_test_role_1), 'The migrated test role 1 was retrieved from the database.');
    $this->assertEqual($migrate_test_role_1->permissions, array(0 => 'migrate test role 1 test permission'));
    $migrate_test_role_2 = entity_load('user_role', 'migrate_test_role_2');
    $this->assertTrue(is_object($migrate_test_role_2), 'The migrated test role 2 was retrieved from the database.');
    $this->assertEqual($migrate_test_role_2->permissions, array(
      'migrate test role 2 test permission',
      'use PHP for settings',
      'administer contact forms',
      'skip comment approval',
      'edit own blog content',
      'edit any blog content',
      'delete own blog content',
      'delete any blog content',
      'create forum content',
      'delete any forum content',
      'delete own forum content',
      'edit any forum content',
      'edit own forum content',
      'administer nodes',
      'access content overview',
    ));
  }

}
