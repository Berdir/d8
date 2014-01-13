<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\d6\MigrateFieldInstanceViewModeTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateViewModesTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate view modes to entity.view_mode.*.*.yml',
      'description'  => 'Migrate view modes',
      'group' => 'Migrate',
    );
  }

  /**
   * Test that migrated view modes can be loaded using D8 API's.
   */
  public function testViewModes() {
    $migration = entity_load('migration', 'd6_view_modes');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test a new view mode.
    $view_mode = entity_load('view_mode', 'node.preview');
    $this->assertEqual(is_null($view_mode), FALSE, 'Preview view mode loaded.');
    $this->assertEqual($view_mode->label(), 'Preview', 'View mode has correct label.');
    // Test the Id Map.
    $this->assertEqual(array('node.preview'), $migration->getIdMap()->lookupDestinationID(array(1)), "Ensure IdMap works.");
  }

}
