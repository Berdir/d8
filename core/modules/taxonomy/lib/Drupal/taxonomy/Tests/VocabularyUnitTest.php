<?php

/**
 * @file
 * Definition of Drupal\taxonomy\Tests\VocabularyUnitTest.
 */

namespace Drupal\taxonomy\Tests;

/**
 * Tests for taxonomy vocabulary functions.
 */
class VocabularyUnitTest extends TaxonomyTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field_test');

  public static function getInfo() {
    return array(
      'name' => 'Taxonomy vocabularies',
      'description' => 'Test loading, saving and deleting vocabularies.',
      'group' => 'Taxonomy',
    );
  }

  function setUp() {
    parent::setUp();

    $admin_user = $this->drupalCreateUser(array('create article content', 'administer taxonomy'));
    $this->drupalLogin($admin_user);
    $this->vocabulary = $this->createVocabulary();
  }

  /**
   * Test deleting a taxonomy that contains terms.
   */
  function testTaxonomyVocabularyDeleteWithTerms() {
    // Delete any existing vocabularies.
    foreach (taxonomy_vocabulary_load_multiple() as $vocabulary) {
      taxonomy_vocabulary_delete($vocabulary->id());
    }

    // Assert that there are no terms left.
    $this->assertEqual(0, db_query('SELECT COUNT(*) FROM {taxonomy_term_data}')->fetchField());

    // Create a new vocabulary and add a few terms to it.
    $vocabulary = $this->createVocabulary();
    $terms = array();
    for ($i = 0; $i < 5; $i++) {
      $terms[$i] = $this->createTerm($vocabulary);
    }

    // Set up hierarchy. term 2 is a child of 1 and 4 a child of 1 and 2.
    $terms[2]->parent = array($terms[1]->tid);
    taxonomy_term_save($terms[2]);
    $terms[4]->parent = array($terms[1]->tid, $terms[2]->tid);
    taxonomy_term_save($terms[4]);

    // Assert that there are now 5 terms.
    $this->assertEqual(5, db_query('SELECT COUNT(*) FROM {taxonomy_term_data}')->fetchField());

    taxonomy_vocabulary_delete($vocabulary->id());

    // Assert that there are no terms left.
    $this->assertEqual(0, db_query('SELECT COUNT(*) FROM {taxonomy_term_data}')->fetchField());
  }

  /**
   * Ensure that the vocabulary static reset works correctly.
   */
  function testTaxonomyVocabularyLoadStaticReset() {
    $original_vocabulary = taxonomy_vocabulary_load($this->vocabulary->id());
    $this->assertTrue(is_object($original_vocabulary), 'Vocabulary loaded successfully.');
    $this->assertEqual($this->vocabulary->name, $original_vocabulary->name, 'Vocabulary loaded successfully.');

    // Change the name and description.
    $vocabulary = $original_vocabulary;
    $vocabulary->name = $this->randomName();
    $vocabulary->description = $this->randomName();
    taxonomy_vocabulary_save($vocabulary);

    // Load the vocabulary.
    $new_vocabulary = taxonomy_vocabulary_load($original_vocabulary->id());
    $this->assertEqual($new_vocabulary->name, $vocabulary->name);
    $this->assertEqual($new_vocabulary->name, $vocabulary->name);

    // Delete the vocabulary.
    taxonomy_vocabulary_delete($this->vocabulary->id());
    $vocabularies = taxonomy_vocabulary_load_multiple();
    $this->assertTrue(!isset($vocabularies[$this->vocabulary->id()]), 'The vocabulary was deleted.');
  }

  /**
   * Tests for loading multiple vocabularies.
   */
  function testTaxonomyVocabularyLoadMultiple() {

    // Delete any existing vocabularies.
    foreach (taxonomy_vocabulary_load_multiple() as $vocabulary) {
      taxonomy_vocabulary_delete($vocabulary->id());
    }

    // Create some vocabularies and assign weights.
    $vocabulary1 = $this->createVocabulary();
    $vocabulary1->weight = 0;
    taxonomy_vocabulary_save($vocabulary1);
    $vocabulary2 = $this->createVocabulary();
    $vocabulary2->weight = 1;
    taxonomy_vocabulary_save($vocabulary2);
    $vocabulary3 = $this->createVocabulary();
    $vocabulary3->weight = 2;
    taxonomy_vocabulary_save($vocabulary3);

    // Fetch the names for all vocabularies, confirm that they are keyed by
    // machine name.
    $names = taxonomy_vocabulary_get_names();
    $this->assertEqual($names[$vocabulary1->id()], $vocabulary1->id(), 'Vocabulary 1 name found.');

    // Fetch all of the vocabularies using taxonomy_vocabulary_load_multiple().
    // Confirm that the vocabularies are ordered by weight.
    $vocabularies = taxonomy_vocabulary_load_multiple();
    taxonomy_vocabulary_sort($vocabularies);
    $this->assertEqual(array_shift($vocabularies)->id(), $vocabulary1->id(), 'Vocabulary was found in the vocabularies array.');
    $this->assertEqual(array_shift($vocabularies)->id(), $vocabulary2->id(), 'Vocabulary was found in the vocabularies array.');
    $this->assertEqual(array_shift($vocabularies)->id(), $vocabulary3->id(), 'Vocabulary was found in the vocabularies array.');

    // Fetch the vocabularies with taxonomy_vocabulary_load_multiple(), specifying IDs.
    // Ensure they are returned in the same order as the original array.
    $vocabularies = taxonomy_vocabulary_load_multiple(array($vocabulary3->id(), $vocabulary2->id(), $vocabulary1->id()));
    $this->assertEqual(array_shift($vocabularies)->id(), $vocabulary3->id(), 'Vocabulary loaded successfully by ID.');
    $this->assertEqual(array_shift($vocabularies)->id(), $vocabulary2->id(), 'Vocabulary loaded successfully by ID.');
    $this->assertEqual(array_shift($vocabularies)->id(), $vocabulary1->id(), 'Vocabulary loaded successfully by ID.');
  }

  /**
   * Tests that machine name changes are properly reflected.
   */
  function testTaxonomyVocabularyChangeMachineName() {
    // Add a field instance to the vocabulary.
    $field = array(
      'field_name' => 'field_test',
      'type' => 'test_field',
    );
    field_create_field($field);
    $instance = array(
      'field_name' => 'field_test',
      'entity_type' => 'taxonomy_term',
      'bundle' => $this->vocabulary->id(),
    );
    field_create_instance($instance);

    // Change the machine name.
    $old_name = $this->vocabulary->id();
    $new_name = drupal_strtolower($this->randomName());
    $this->vocabulary->vid = $new_name;
    taxonomy_vocabulary_save($this->vocabulary);

    // Check that entity bundles are properly updated.
    $info = entity_get_bundles('taxonomy_term');
    $this->assertFalse(isset($info[$old_name]), 'The old bundle name does not appear in entity_get_bundles().');
    $this->assertTrue(isset($info[$new_name]), 'The new bundle name appears in entity_get_bundles().');

    // Check that the field instance is still attached to the vocabulary.
    $this->assertTrue(field_info_instance('taxonomy_term', 'field_test', $new_name), 'The bundle name was updated correctly.');
  }

  /**
   * Test uninstall and reinstall of the taxonomy module.
   */
  function testUninstallReinstall() {
    // Fields and field instances attached to taxonomy term bundles should be
    // removed when the module is uninstalled.
    $this->field_name = drupal_strtolower($this->randomName() . '_field_name');
    $this->field = array('field_name' => $this->field_name, 'type' => 'text', 'cardinality' => 4);
    $this->field = field_create_field($this->field);
    $this->instance = array(
      'field_name' => $this->field_name,
      'entity_type' => 'taxonomy_term',
      'bundle' => $this->vocabulary->id(),
      'label' => $this->randomName() . '_label',
    );
    field_create_instance($this->instance);

    module_disable(array('taxonomy'));
    require_once DRUPAL_ROOT . '/core/includes/install.inc';
    module_uninstall(array('taxonomy'));
    module_enable(array('taxonomy'));

    // Now create a vocabulary with the same name. All field instances
    // connected to this vocabulary name should have been removed when the
    // module was uninstalled. Creating a new field with the same name and
    // an instance of this field on the same bundle name should be successful.
    $this->vocabulary->enforceIsNew();
    taxonomy_vocabulary_save($this->vocabulary);
    unset($this->field['id']);
    field_create_field($this->field);
    field_create_instance($this->instance);
  }
}
