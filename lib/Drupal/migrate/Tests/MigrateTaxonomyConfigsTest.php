<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateTaxonomyConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables from the Taxonomy module.
 */
class MigrateTaxonomyConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to taxonomy.settings.yml',
      'description'  => 'Upgrade variables to taxonomy.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of taxonomy variables to taxonomy.settings.yml.
   */
  public function testTaxonomySettings() {
    $migration = entity_load('migration', 'd6_taxonomy_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6TaxonomySettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('taxonomy.settings');
    $this->assertIdentical($config->get('terms_per_page_admin'), 100);
    $this->assertIdentical($config->get('override_selector'), FALSE);
  }
}
