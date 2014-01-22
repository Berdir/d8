<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateCommentTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\comment\Entity\Comment;
use Drupal\Core\Language\Language;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test the comment migration.
 */
class MigrateCommentTest extends MigrateDrupalTestBase {

  static $modules = array('comment');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate comments.',
      'description'  => 'Upgrade comments.',
      'group' => 'Migrate Drupal',
    );
  }

  public function testComments() {
    $table_name = entity_load('migration', 'd6_filter_format')->getIdMap()->getMapTableName();
    $database = \Drupal::database();
    // We need some sample data so we can use the Migration process plugin.
    $database->insert($table_name)->fields(array(
      'sourceid1',
      'destid1',
    ))
    ->values(array(
      'sourceid1' => 1,
      'destid1' => 'plain_text',
    ))
    ->execute();

    $table_name = entity_load('migration', 'd6_node_format')->getIdMap()->getMapTableName();
    // We need some sample data so we can use the Migration process plugin.
    $database->insert($table_name)->fields(array(
      'sourceid1',
      'destid1',
    ))
      ->values(array(
        'sourceid1' => 1,
        'destid1' => 1,
      ))
      ->execute();
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_comment');

    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Comment.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    /** @var Comment $comment */
    $comment = entity_load('comment', 1);
    $this->assertEqual('The first comment', $comment->subject);
    $this->assertEqual('The first comment body.', $comment->body->value);
    $this->assertEqual('text_plain', $comment->body->format);
    $this->assertEqual(0, $comment->pid);
    $this->assertEqual(1, $comment->entity_id);
    $this->assertEqual('story', $comment->entity_type);
    $this->assertEqual(Language::LANGCODE_NOT_SPECIFIED, $comment->language()->id);
  }
}
