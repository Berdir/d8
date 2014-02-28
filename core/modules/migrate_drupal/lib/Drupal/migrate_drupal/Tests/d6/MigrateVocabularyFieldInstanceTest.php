<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateVocabularyFieldInstanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateVocabularyFieldInstanceTest extends MigrateDrupalTestBase {

static $modules = array('node', 'field', 'taxonomy');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Vocabulary field instance migration',
      'description'  => 'Vocabulary field instance migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the vocabulary field instance migration.
   */
  public function testVocabularyFieldInstance() {

    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_taxonomy_vocabulary' => array(
        array(array(1), array('tags')),
      ),
      'd6_vocabulary_field' => array(
        array(array(1), array('node', 'tags')),
      )
    );
    $this->prepareIdMappings($id_mappings);

    // Create the vocab.
    entity_create('taxonomy_vocabulary', array(
      'name' => 'Test Vocabulary',
      'description' => 'Test Vocabulary',
      'vid' => 'tags',
    ))->save();
    // Create the field itself.
    entity_create('field_config', array(
      'entity_type' => 'node',
      'name' => 'tags',
      'type' => 'taxonomy_term_reference',
    ))->save();

    $migration = entity_load('migration', 'd6_vocabulary_field_instance');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6VocabularyField.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test that the field exists.
    $field_id = 'node.article.tags';
    $field = entity_load('field_instance_config', $field_id);
    $this->assertEqual($field->id(), $field_id, 'Field instance exists on article bundle.');
    $settings = $field->getSettings();
    $this->assertEqual('tags', $settings['allowed_values'][0]['vocabulary'], "Vocabulary has correct settings.");

    // Test the page bundle as well.
    $field_id = 'node.page.tags';
    $field = entity_load('field_instance_config', $field_id);
    $this->assertEqual($field->id(), $field_id, 'Field instance exists on page bundle.');
    $settings = $field->getSettings();
    $this->assertEqual('tags', $settings['allowed_values'][0]['vocabulary'], "Vocabulary has correct settings.");

    $this->assertEqual(array('node', 'article', 'tags'), $migration->getIdMap()->lookupDestinationID(array(1, 'article')));
  }

}
