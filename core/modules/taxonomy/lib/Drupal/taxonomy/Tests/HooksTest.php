<?php

/**
 * @file
 * Definition of Drupal\taxonomy\Tests\HooksTest.
 */

namespace Drupal\taxonomy\Tests;

/**
 * Tests for taxonomy hook invocation.
 */
class HooksTest extends TaxonomyTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('taxonomy_test');

  public static function getInfo() {
    return array(
      'name' => 'Taxonomy term hooks',
      'description' => 'Hooks for taxonomy term load/save/delete.',
      'group' => 'Taxonomy',
    );
  }

  function setUp() {
    parent::setUp();

    $taxonomy_admin = $this->drupalCreateUser(array('administer taxonomy'));
    $this->drupalLogin($taxonomy_admin);
  }

  /**
   * Test hooks on CRUD of terms.
   *
   * Test that hooks are run correctly on creating, editing, viewing,
   * and deleting a term.
   *
   * @see taxonomy_test.module
   */
  function testTaxonomyTermHooks() {
    $vocabulary = $this->createVocabulary();

    // Create a term with one antonym.
    $edit = array(
      'name' => $this->randomName(),
      'antonym' => 'Long',
    );
    $this->drupalPost('admin/structure/taxonomy/' . $vocabulary->id() . '/add', $edit, t('Save'));
    $terms = taxonomy_term_load_multiple_by_name($edit['name']);
    $term = reset($terms);
    $this->assertEqual($term->antonym, $edit['antonym'], 'Antonym was loaded into the term object.');

    // Update the term with a different antonym.
    $edit = array(
      'name' => $this->randomName(),
      'antonym' => 'Short',
    );
    $this->drupalPost('taxonomy/term/' . $term->tid . '/edit', $edit, t('Save'));
    taxonomy_terms_static_reset();
    $term = taxonomy_term_load($term->tid);
    $this->assertEqual($edit['antonym'], $term->antonym, 'Antonym was successfully edited.');

    // View the term and ensure that hook_taxonomy_term_view() and
    // hook_entity_view() are invoked.
    $term = taxonomy_term_load($term->tid);
    module_load_include('inc', 'taxonomy', 'taxonomy.pages');
    $term_build = taxonomy_term_page($term);
    $this->assertFalse(empty($term_build['taxonomy_terms'][$term->tid]['taxonomy_test_term_view_check']), 'hook_taxonomy_term_view() was invoked when viewing the term.');
    $this->assertFalse(empty($term_build['taxonomy_terms'][$term->tid]['taxonomy_test_entity_view_check']), 'hook_entity_view() was invoked when viewing the term.');

    // Delete the term.
    taxonomy_term_delete($term->tid);
    $antonym = db_query('SELECT tid FROM {taxonomy_term_antonym} WHERE tid = :tid', array(':tid' => $term->tid))->fetchField();
    $this->assertFalse($antonym, 'The antonym were deleted from the database.');
  }
}
