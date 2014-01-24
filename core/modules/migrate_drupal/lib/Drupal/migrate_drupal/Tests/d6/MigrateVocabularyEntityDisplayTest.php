<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateVocabularyEntityDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateVocabularyEntityDisplayTest extends MigrateDrupalTestBase {

static $modules = array('taxonomy', 'field');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Vocabulary entity display migration',
      'description'  => 'Vocabulary entity display migration',
      'group' => 'Migrate Drupal',
    );
  }

  function testVocabularyEntityDisplay() {

    // Loading the migration creates the map table so we can insert our data.
    $table_name = entity_load('migration', 'd6_taxonomy_vocabulary')->getIdMap()->getMapTableName();
    // We need some sample data so we can use the Migration process plugin.
    \Drupal::database()->insert($table_name)->fields(array(
      'sourceid1',
      'destid1',
    ))
    ->values(array(
      'sourceid1' => 1,
      'destid1' => 'tags',
    ))
    ->execute();

    $migration = entity_load('migration', 'd6_vocabulary_entity_display');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6VocabularyField.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test that the field exists.
    $component = entity_get_display('node', 'page', 'default')->getComponent('tags');
    $this->assertEqual($component['type'], 'taxonomy_term_reference');
    $this->assertEqual($component['weight'], 20);
    // Test the Id map.
    $this->assertEqual(array('node', 'article', 'default', 'tags'), $migration->getIdMap()->lookupDestinationID(array(1, 'article')));
  }

}
