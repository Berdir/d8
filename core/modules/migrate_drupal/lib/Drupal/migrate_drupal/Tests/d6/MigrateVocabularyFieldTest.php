<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateVocabularyToFieldTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateVocabularyFieldTest extends MigrateDrupalTestBase {

static $modules = array('taxonomy', 'field');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Vocabulary field migration',
      'description'  => 'Vocabulary field migration',
      'group' => 'Migrate Drupal',
    );
  }

  function testVocabularyField() {

    // Loading the migration creates the map table so we can insert our data.
    $table_name = entity_load('migration', 'd6_taxonomy_vocabulary')->getIdMap()->mapTableName();
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

    entity_create('taxonomy_vocabulary', array(
      'name' => 'Test Vocabulary',
      'description' => 'Test Vocabulary',
      'vid' => 'test_vocab',
    ))->save();

    $migration = entity_load('migration', 'd6_vocabulary_field');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6VocabularyField.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test that the field exists.
    $field_id = 'node.tags';
    $field = entity_load('field_entity', $field_id);
    $this->assertEqual($field->id(), $field_id);
    $settings = $field->getSettings();
    $this->assertEqual('tags', $settings['allowed_values'][0]['vocabulary'], "Vocabulary has correct settings.");
    $this->assertEqual(array('node', 'tags'), $migration->getIdMap()->lookupDestinationID(array(1)), "Test IdMap");

  }

}
