<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSearchPageTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;
use Drupal\Core\Database\Database;

class MigrateSearchPageTest extends MigrateDrupalTestBase {

  static $modules = array('search');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate search settings to search.page.*.yml',
      'description'  => 'Upgrade search rank settings to search.page.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  function testSearchPage() {
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_search_page');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SearchPage.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $id = 'node_search';
    /** @var \Drupal\search\Entity\SearchPage $search_page */
    $search_page = entity_load('search_page', $id);
    $this->assertEqual($search_page->id(), $id);
    $configuration = $search_page->getPlugin()->getConfiguration();
    $this->assertEqual($configuration['rankings'], array(
      'comments' => 5,
      'relevance' => 2,
      'sticky' => 8,
      'views' => 1,
    ));
    $this->assertEqual($search_page->getPath(), 'node');

    // Test that we can re-import using the EntitySearchPage destination.
    Database::getConnection('default', 'migrate')
      ->update('variable')
      ->fields(array('value' => serialize(4)))
      ->condition('name', 'node_rank_comments')
      ->execute();

    $migration = entity_load_unchanged('migration', 'd6_search_page');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $search_page = entity_load('search_page', $id);
    $configuration = $search_page->getPlugin()->getConfiguration();
    $this->assertEqual($configuration['rankings']['comments'], 4);
  }

}
