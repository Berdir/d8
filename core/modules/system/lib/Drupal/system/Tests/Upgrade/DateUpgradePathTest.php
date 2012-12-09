<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Upgrade\DateUpgradePathTest.
 */

namespace Drupal\system\Tests\Upgrade;

/**
 * Test upgrade of date formats.
 */
class DateUpgradePathTest extends UpgradePathTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Date upgrade test',
      'description' => 'Upgrade tests for date formats.',
      'group' => 'Upgrade path',
    );
  }

  public function setUp() {
    $this->databaseDumpFiles = array(
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.bare.standard_all.database.php.gz',
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.date.database.php',
    );
    parent::setUp();
  }

  /**
   * Tests that date formats have been upgraded.
   */
  public function testDateUpgrade() {
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');

    // Verify standard date formats
    $expected_formats['short'] = array(
      'name' => 'Short',
      'pattern' => array(
        'php' => 'Y/m/d - H:i',
      ),
      'locked' => '1',
    );
    $expected_formats['medium'] = array(
      'name' => 'Medium',
      'pattern' => array(
        'php' => 'D, d/m/Y - H:i',
      ),
      'locked' => '1',
    );
    $expected_formats['long'] = array(
      'name' => 'Long',
      'pattern' => array(
        'php' => 'l, Y,  F j - H:i',
      ),
      'locked' => '1',
    );

    // Verify custom date format
    $expected_formats['test_custom'] = array(
      'name' => 'Test Custom',
      'pattern' => array(
        'php' => 'd m Y',
        ),
      'locked' => '0',
    );

    foreach ($expected_formats as $type => $format) {
      $format_info = config('system.date')->get('formats.' . $type);

      $this->assertEqual($format_info['name'], $format['name'], "Config value for {$type} name is the same");
      $this->assertEqual($format_info['locked'], $format['locked'], "Config value for {$type} locked is the same");
      $this->assertEqual($format_info['pattern']['php'], $format['pattern']['php'], "Config value for {$type} PHP date pattern is the same");
    }
  }
}
