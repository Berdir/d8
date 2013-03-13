<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Tests\Upgrade\EntityReferenceUpgradePathTest.
 */

namespace Drupal\entity_reference\Tests\Upgrade;

use Drupal\system\Tests\Upgrade\UpgradePathTestBase;

/**
 * Performs major version release upgrade tests on a populated database.
 *
 * Loads an installation of Drupal 7.x and runs the upgrade process on it.
 *
 * The install contains the standard profile modules (along with generated
 * content) so that an update from of a site under this profile may be tested.
 */
class EntityReferenceUpgradePathTest extends UpgradePathTestBase {
  public static function getInfo() {
    return array(
      'name'  => 'Entity reference upgrade test',
      'description'  => 'Upgrade tests for taxonomy term fields.',
      'group' => 'Upgrade path',
    );
  }

  public function setUp() {
    // Path to the database dump files.
    $this->databaseDumpFiles = array(
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.filled.standard_all.database.php.gz',
    );
    parent::setUp();
  }

  /**
   * Tests a successful point release update.
   */
  public function testEntityReferenceUpgrade() {
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');

    // Check that the default node page is displayed successfully.
    $this->drupalGet('node');
    $this->assertResponse(200, 'Node page displayed successfully.');

    // Check that a node that previously had a taxonomy term reference field can
    // still be edited (Node 8 is a page node with taxonomy fields filled).
    $this->drupalGet('node/8/edit');
    $this->assertResponse(200, 'Node edit page displayed successfully.');

    // Check that a node that previously had a taxonomy term reference field can
    // still be added.
    $this->drupalGet('node/add/page');
    $this->assertResponse(200, 'Node add page displayed successfully.');

    // Check that a Term reference field has been converted to Entity reference.
    $taxonomy_term_field = field_info_field('taxonomy_vocabulary_1_0');
    $this->assertTrue($taxonomy_term_field['type'] == 'entity_reference', 'Term reference field is now a entity reference field.');

    // Check that a Term reference instance has been converted to Entity
    // reference.
    $taxonomy_term_field_instance = field_info_instance('node', 'taxonomy_vocabulary_1_0', 'page');
    $this->assertTrue(isset($taxonomy_term_field_instance['settings']['handler_settings']), 'Term reference field instance is now a entity reference field instance.');
  }
}
