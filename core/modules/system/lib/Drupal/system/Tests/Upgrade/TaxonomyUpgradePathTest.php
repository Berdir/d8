<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\TaxonomyUpgradePathTest.
 */

namespace Drupal\system\Tests\Upgrade;

/**
 * Tests upgrading a bare database with user role data.
 *
 * Loads a standard installation of Drupal 7 with taxonomy data and runs the
 * upgrade process on it. Tests for the conversion taxonomy vocabularies and
 * term description field.
 */
class TaxonomyUpgradePathTest extends UpgradePathTestBase {
  public static function getInfo() {
    return array(
      'name'  => 'Taxonomy upgrade test',
      'description'  => 'Taxonomy vocabulary and term upgrade tests.',
      'group' => 'Upgrade path',
    );
  }

  public function setUp() {
    $this->databaseDumpFiles = array(
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.bare.standard_all.database.php.gz',
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.taxonomy.database.php',
    );
    parent::setUp();
  }

  /**
   * Tests expected role ID conversions after a successful upgrade.
   */
  public function testRoleUpgrade() {
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');

    // Check the tags vocabulary.
    $vocabulary = taxonomy_vocabulary_load('tags');
    $this->assertEqual($vocabulary->label(), 'Tags');
    $this->assertEqual($vocabulary->description, 'Use tags to group articles on similar topics into categories.');
    $this->assertTrue($vocabulary->uuid());

    // Load the two prepared terms and assert them.
    $term1 = taxonomy_term_load(5);
    $this->assertEqual($term1->label(), 'A tag');
    $this->assertEqual($term1->vid->value, 'tags');
    $this->assertEqual($term1->bundle(), 'tags');
    $this->assertEqual($term1->taxonomy_term_description->value, 'Description of a tag');
    $this->assertEqual($term1->taxonomy_term_description->format, 'plain_text');

    $term2 = taxonomy_term_load(6);
    $this->assertEqual($term2->label(), 'Another tag');
    $this->assertEqual($term2->vid->value, 'tags');
    $this->assertEqual($term2->bundle(), 'tags');
    $this->assertEqual($term2->taxonomy_term_description->value, '<strong>HTML</strong> Description');
    $this->assertEqual($term2->taxonomy_term_description->format, 'filtered_html');
  }

}
