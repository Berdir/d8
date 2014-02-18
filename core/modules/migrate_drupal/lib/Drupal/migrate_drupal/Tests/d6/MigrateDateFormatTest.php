<?php

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;
use Drupal\Core\Database\Database;

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

  public function testDateFormats() {
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

    // Test that we can re-import using the EntityDateFormat destination.
    Database::getConnection('default', 'migrate')
      ->update('variable')
      ->fields(array('value' => serialize('\S\H\O\R\T d/m/Y - H:i')))
      ->condition('name', 'date_format_short')
      ->execute();
    db_truncate($migration->getIdMap()->mapTableName())->execute();
    $migration = entity_load_unchanged('migration', 'd6_date_formats');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $short_date_format = entity_load('date_format', 'short');
    $this->assertEqual('\S\H\O\R\T d/m/Y - H:i', $short_date_format->getPattern(DrupalDateTime::PHP));

  }

}
