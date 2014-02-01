<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserProfileEntityDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of user profile fields.
 */
class MigrateUserProfileEntityDisplayTest extends MigrateDrupalTestBase {

  static $modules = array('link', 'options');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate user profile entity display',
      'description'  => 'Test the user profile entity display migration.',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests migration of user profile fields.
   */
  public function testUserProfileFields() {
    $migration = entity_load('migration', 'd6_user_profile_entity_display');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserProfileFields.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $display = entity_get_display('user', 'user', 'default');

    // Test a text field.
    $component = $display->getComponent('profile_color');
    $this->assertEqual($component['type'], 'text_default');

    // Test a list field.
    $component = $display->getComponent('profile_bands');
    $this->assertEqual($component['type'], 'text_default');

    // Test a date field.
    $component = $display->getComponent('profile_birthdate');
    $this->assertEqual($component['type'], 'datetime_default');

    // Test PROFILE_PRIVATE field is hidden.
    $this->assertNull($display->getComponent('profile_sell_address'));

    // Test PROFILE_HIDDEN field is hidden.
    $this->assertNull($display->getComponent('profile_sold_to'));
  }

}
