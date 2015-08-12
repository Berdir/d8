<?php

/**
 * @file
 * Contains \Drupal\views\Tests\Update\CacheabilityMetadataUpdateTest.
 */

namespace Drupal\views\Tests\Update;

use Drupal\system\Tests\Update\UpdatePathTestBase;
use Drupal\views\Views;

/**
 * Tests that views update hooks are properly run.
 *
 * @see views_update_8002().
 *
 * @group Update
 */
class CacheabilityMetadataUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->databaseDumpFiles = [__DIR__ . '/../../../../system/tests/fixtures/update/drupal-8.0.0-cdd12a9.standard.php.gz'];
    parent::setUp();
  }

  /**
   * Tests that views cacheability metadata updated properly.
   */
  public function testUpdateHookN() {
    // Verify that the 8001 schema is in place.
    $this->assertEqual(drupal_get_installed_schema_version('views'), 8001);
    foreach (Views::getAllViews() as $view) {
      $displays = $view->get('display');
      foreach (array_keys($displays) as $display_id) {
        $display = $view->getDisplay($display_id);
        $this->assertTrue(isset($display['cache_metadata']['cacheable']));
      }
    }
    $this->runUpdates();
    // Ensure schema has changed.
    $this->assertEqual(drupal_get_installed_schema_version('views', TRUE), 8002);
    foreach (Views::getAllViews() as $view) {
      $displays = $view->get('display');
      foreach (array_keys($displays) as $display_id) {
        $display = $view->getDisplay($display_id);
        $this->assertFalse(isset($display['cache_metadata']['cacheable']));
        $this->assertTrue(isset($display['cache_metadata']['contexts']));
        $this->assertTrue(isset($display['cache_metadata']['max_age']));
        $this->assertTrue(isset($display['cache_metadata']['tags']));
      }
    }
  }

}
