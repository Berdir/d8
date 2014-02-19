<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserRoleTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUserRoleTest extends MigrateDrupalTestBase {

  static $modules = array('filter');

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

  /**
   * Tests user role migration.
   */
  public function testUserRole() {
    // We need some sample data so we can use the Migration process plugin.
    $table_name = entity_load('migration', 'd6_filter_format')->getIdMap()->mapTableName();
    db_insert($table_name)->fields(array(
      'sourceid1',
      'destid1',
    ))
    ->values(array(
      'sourceid1' => 1,
      'destid1' => 'filtered_html',
    ))
    ->values(array(
      'sourceid1' => 2,
      'destid1' => 'full_html',
    ))
    ->execute();

    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_user_role');
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserRole.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FilterFormat.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $rid = 'anonymous';
    $anonymous = entity_load('user_role', $rid);
    $this->assertEqual($anonymous->id(), $rid);
    $this->assertEqual($anonymous->getPermissions(), array('migrate test anonymous permission', 'use text format filtered_html'));
    $this->assertEqual(array($rid), $migration->getIdMap()->lookupDestinationId(array(1)));
    $rid = 'authenticated';
    $authenticated = entity_load('user_role', $rid);
    $this->assertEqual($authenticated->id(), $rid);
    $this->assertEqual($authenticated->getPermissions(), array('migrate test authenticated permission', 'use text format filtered_html'));
    $this->assertEqual(array($rid), $migration->getIdMap()->lookupDestinationId(array(2)));
    $rid = 'migrate_test_role_1';
    $migrate_test_role_1 = entity_load('user_role', $rid);
    $this->assertEqual($migrate_test_role_1->id(), $rid);
    $this->assertEqual($migrate_test_role_1->getPermissions(), array(0 => 'migrate test role 1 test permission', 'use text format full_html'));
    $this->assertEqual(array($rid), $migration->getIdMap()->lookupDestinationId(array(3)));
    $rid = 'migrate_test_role_2';
    $migrate_test_role_2 = entity_load('user_role', $rid);
    $this->assertEqual($migrate_test_role_2->getPermissions(), array(
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
    $this->assertEqual($migrate_test_role_2->id(), $rid);
    $this->assertEqual(array($rid), $migration->getIdMap()->lookupDestinationId(array(4)));
  }

}
