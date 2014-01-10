<?php

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of date formats.
 */
class MigrateDateFormatTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate date formats to system.date_format.*.yml',
      'description'  => 'Upgrade date formats to system.date_format.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  function testDateFormats() {
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_date_formats');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6DateFormat.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();

    $short_date_format = entity_load('date_format', 'short');
    $this->assertEqual('\S\H\O\R\T m/d/Y - H:i', $short_date_format->getPattern(DrupalDateTime::PHP));

    $medium_date_format = entity_load('date_format', 'medium');
    $this->assertEqual('\M\E\D\I\U\M D, m/d/Y - H:i', $medium_date_format->getPattern(DrupalDateTime::PHP));

    $long_date_format = entity_load('date_format', 'long');
    $this->assertEqual('\L\O\N\G l, F j, Y - H:i', $long_date_format->getPattern(DrupalDateTime::PHP));
  }

}
