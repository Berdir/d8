<?php

/**
 * @file
 * Database additions for Drupal\system\Tests\Upgrade\DateUpgradePathTest.
 *
 * This dump only contains data and schema components relevant for date
 * functionality. The bare.standard_all.database.php file is imported before
 * this dump, so the two form the database structure expected in tests
 * altogether.
 */
// Add default format for standard date formats
db_insert('variable')->fields(array(
  'name',
  'value',
))
->values(array(
  'name' => 'date_format_short',
  'value'=> 's:11:"Y/m/d - H:i";',
))
->values(array(
  'name' => 'date_format_medium',
  'value'=> 's:14:"D, d/m/Y - H:i";',
))
->values(array(
  'name' => 'date_format_long',
  'value'=> 's:16:"l, Y,  F j - H:i";',
))
->values(array(
  'name' => 'date_format_test_custom',
  'value'=> 's:5:"d m Y";',
))->execute();

// Add custom date formats.
db_insert('date_format_type')->fields(array(
  'type',
  'title',
  'locked'
))
// Custom date format.
->values(array(
  'type' => 'test_custom',
  'title' => 'Test Custom',
  'locked' => '0',
))
->execute();

// Add image effects.
db_insert('date_formats')->fields(array(
  'dfid',
  'format',
  'type',
  'locked',
))
->values(array(
  'dfid' => '36',
  'format' => 'd m Y',
  'type' => 'test_custom',
  'locked' => '0',
))
->execute();
