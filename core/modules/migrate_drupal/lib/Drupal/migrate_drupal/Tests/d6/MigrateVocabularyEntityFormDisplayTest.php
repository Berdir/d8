<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateVocabularyEntityFormDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateVocabularyEntityFormDisplayTest extends MigrateDrupalTestBase {

static $modules = array('taxonomy', 'field');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Vocabulary entity form display migration',
      'description'  => 'Vocabulary entity form display migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the vocabulary entity form display migration.
   */
  public function testVocabularyEntityFormDisplay() {

    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_taxonomy_vocabulary' => array(
        array(array(1), array('tags')),
      ),
      'd6_vocabulary_field_instance' => array(
        array(array(1, 'page'), array('node', 'page', 'tags')),
      )
    );
    $this->prepareIdMappings($id_mappings);

    $migration = entity_load('migration', 'd6_vocabulary_entity_form_display');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6VocabularyField.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    // Test that the field exists.
    $component = entity_get_form_display('node', 'page', 'default')->getComponent('tags');
    $this->assertEqual($component['type'], 'taxonomy_term_reference');
    $this->assertEqual($component['weight'], 20);
    // Test the Id map.
    $this->assertEqual(array('node', 'article', 'default', 'tags'), $migration->getIdMap()->lookupDestinationID(array(1, 'article')));
  }

}
