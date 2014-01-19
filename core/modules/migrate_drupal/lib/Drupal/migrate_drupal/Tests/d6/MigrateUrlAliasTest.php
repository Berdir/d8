<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUrlAliasTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test the url alias migration.
 */
class MigrateUrlAliasTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Url alias migration.',
      'description'  => 'Url alias migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the url alias migration.
   */
  public function testUrlAlias() {

    $migration = entity_load('migration', 'd6_url_alias');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UrlAlias.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test that the field exists.
    $conditions = array(
      'source' => 'node/1',
      'alias' => 'alias-one',
      'langcode' => 'en',
    );
    $path = \Drupal::service('path.crud')->load($conditions);
    $this->assertNotNull($path, "Path alias for node/1 successfully loaded.");
    $this->assertEqual(array(1), $migration->getIdMap()->lookupDestinationID(array($path['pid'])), "Test IdMap");
    $conditions = array(
      'source' => 'node/2',
      'alias' => 'alias-two',
      'langcode' => 'en',
    );
    $path = \Drupal::service('path.crud')->load($conditions);
    $this->assertNotNull($path, "Path alias for node/2 successfully loaded.");

  }

}
