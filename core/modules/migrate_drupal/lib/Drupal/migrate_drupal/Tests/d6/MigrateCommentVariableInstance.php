<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateCommentVariableInstance.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateCommentVariableInstance extends MigrateDrupalTestBase {

  static $modules = array('comment');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate comment variables to a field instance,',
      'description'  => 'Upgrade comment variables  to field.instance.node.comment.yml',
      'group' => 'Migrate Drupal',
    );
  }

  function testCommentField() {
    foreach (array('page', 'story') as $type) {
      entity_create('node_type', array('type' => $type))->save();
    }
    entity_create('field_entity', array(
      'entity_type' => 'node',
        'name' => 'comment',
        'type' => 'comment',
        'translatable' => '0',
    ))->save();
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_comment_field_instance');
    $dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6CommentVariable.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $node = entity_create('node', array('type' => 'page'));
    $this->assertEqual($node->comment->status, 0);
    $node = entity_create('node', array('type' => 'story'));
    $this->assertEqual($node->comment->status, 2);
    $settings = $node->comment->getFieldDefinition()->getSettings();
    $this->assertEqual($settings['default_mode'], 2);
    $this->assertEqual($settings['per_page'], 70);
    $this->assertEqual($settings['anonymous'], 1);
    $this->assertEqual($settings['subject'], 0);
    $this->assertEqual($settings['form_location'], 0);
    $this->assertEqual($settings['preview'], 0);
  }

}
