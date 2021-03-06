<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateForumConfigsTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;

/**
 * Tests migration of variables for the Forum module.
 */
class MigrateForumConfigsTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to forum.settings.yml',
      'description'  => 'Upgrade variables to forum.settings.yml',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests migration of forum variables to forum.settings.yml.
   */
  public function testForumSettings() {
    $migration = entity_load('migration', 'd6_forum_settings');
    $dumps = array(
      drupal_get_path('module', 'migrate') . '/lib/Drupal/migrate/Tests/Dump/Drupal6ForumSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('forum.settings');
    $this->assertIdentical($config->get('topics.hot_threshold'), '15');
    $this->assertIdentical($config->get('topics.page_limit'), '25');
    $this->assertIdentical($config->get('topics.order'), '1');
    // The vocabulary vid depends on existing vids when the Forum module was enabled. This would have to be user-selectable based on a query to the D6 vocabulary table.
    //$this->assertIdentical($config->get('forum_nav_vocabulary'), '1');
    // This is 'forum_block_num_0' in D6, but block:active:limit' in D8.
    $this->assertIdentical($config->get('block.active.limit'), '5');
    // This is 'forum_block_num_1' in D6, but 'block:new:limit' in D8.
    $this->assertIdentical($config->get('block.new.limit'), '5');
  }
}
