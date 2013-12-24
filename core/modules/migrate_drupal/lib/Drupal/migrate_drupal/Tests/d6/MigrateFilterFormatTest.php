<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateD6FilterFormatTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateFilterFormatTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to filter.formats.*.yml',
      'description'  => 'Upgrade variables to filter.formats.*.yml',
      'group' => 'Migrate',
    );
  }

  function testFilterFormat() {
    $migration = entity_load('migration', 'd6_filter_format');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FilterFormat.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();
    $filter_format = entity_load('filter_format', 'filtered_html');

    // Check filter status.
    $filters = $filter_format->get('filters');
    foreach ($filters as $filter) {
      $this->assertTrue($filter['status']);
    }

    //Check variables migrated into filter
    $this->assertIdentical($filters['filter_html_escape']['settings']['allowed_html'], '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd>');
    $this->assertIdentical($filters['filter_html_escape']['settings']['filter_html_help'], 1);
    $this->assertIdentical($filters['filter_html']['settings']['filter_html_nofollow'], 0);
    $this->assertIdentical($filters['filter_url']['settings']['filter_url_length'] , '72');
  }

}
