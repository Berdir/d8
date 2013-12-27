<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateTaxonomyVocabularyTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateTaxonomyVocabularyTest extends MigrateDrupalTestBase {

  public static $modules = array('taxonomy');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate taxonomy vocabularies.',
      'description'  => 'Migrate taxonomy vocabularies to taxonomy.vocabulary.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  function testContactCategory() {
    $migration = entity_load('migration', 'd6_taxonomy_vocabulary');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TaxonomyVocabulary.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();

    $vocabulary = entity_load('taxonomy_vocabulary', 'vocabulary_1_i_0_');
  }
}
