<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateTaxonomyTermTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Tests migration of taxonomy terms.
 */
class MigrateTaxonomyTermTest extends MigrateDrupalTestBase {

  static $modules = array('taxonomy');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate taxonomy terms',
      'description'  => 'Upgrade taxonomy terms',
      'group' => 'Migrate Drupal',
    );
  }

  public function testTaxonomyTerms() {
    $this->prepareIdMappings(array(
      'd6_taxonomy_vocabulary' => array(
        array(array(1), array(1)),
        array(array(2), array(2)),
        array(array(3), array(3)),
    )));
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_taxonomy_term');
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TaxonomyTerm.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TaxonomyVocabulary.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $expected_results = array(
      '1' => array(
        'vid' => 1,
        'weight' => 0,
      ),
      '2' => array(
        'vid' => 2,
        'weight' => 3,
      ),
      '3' => array(
        'vid' => 2,
        'weight' => 4,
        'parent' => array(2),
      ),
      '4' => array(
        'vid' => 3,
        'weight' => 6,
      ),
      '5' => array(
        'vid' => 3,
        'weight' => 7,
        'parent' => array(4),
      ),
      '6' => array(
        'vid' => 3,
        'weight' => 8,
        'parent' => array(4, 5),
      ),
    );
    $terms = entity_load_multiple('taxonomy_term', array_keys($expected_results));
    foreach ($expected_results as $tid => $values) {
      /** @var Term $term */
      $term = $terms[$tid];
      $this->assertIdentical($term->name->value, "term {$tid} of vocabulary {$values['vid']}");
      $this->assertIdentical($term->description->value, "description of term {$tid} of vocabulary {$values['vid']}");
      $this->assertEqual($term->vid->value, $values['vid']);
      $this->assertEqual($term->weight->value, $values['weight']);
      if (empty($values['parent'])) {
        $this->assertNull($term->parent->value);
      }
      else {
        $parents = array();
        foreach (taxonomy_term_load_parents($tid) as $parent) {
          $parents[] = $parent->id();
        }
        $this->assertEqual($values['parent'], $parents);
      }
    }
  }

}
