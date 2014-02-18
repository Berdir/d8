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

  static $modules = array('node', 'comment');

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
    $id_mappings = array(
      'd6_filter_format' => array(array(array(1), array('plain_text'))),
    );
    $this->prepareIdMappings($id_mappings);
    #entity_create('node_type', array('type' => 'page', 'name' => 'page'))->save();
    \Drupal::service('comment.manager')->addDefaultField('node', 'page');
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
    $this->assertEqual('The first comment.', $comment->subject->value);
    $this->assertEqual('The first comment body.', $comment->comment_body->value);
    $this->assertEqual('plain_text', $comment->comment_body->format);
    $this->assertEqual(0, $comment->pid->value);
    $this->assertEqual(1, $comment->entity_id->value);
    $this->assertEqual('node', $comment->entity_type->value);
    $this->assertEqual(Language::LANGCODE_NOT_SPECIFIED, $comment->language()->id);

    $comment = entity_load('comment', 2);
    $this->assertEqual('The response to the second comment.', $comment->subject->value);
    $this->assertEqual(3, $comment->pid->value);

    $comment = entity_load('comment', 3);
    $this->assertEqual('The second comment.', $comment->subject->value);
    $this->assertEqual(0, $comment->pid->value);
  }
}
