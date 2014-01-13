<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateCommentVariableEntityDisplay.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;

/**
 * Tests comment variables migrated into an entity display.
 */
class MigrateCommentVariableEntityDisplay extends MigrateCommentVariableDisplayBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate comment variables to entity displays,',
      'description'  => 'Upgrade comment variables to entity.display.node.*.default.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests comment variables migrated into an entity display.
   */
  public function testCommentEntityDisplay() {
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_comment_entity_display');
    $this->prepare($migration, $this->dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    foreach ($this->types as $type) {
      $component = entity_get_display('node', $type, 'default')->getComponent('comment');
      $this->assertEqual($component['label'], 'hidden');
      $this->assertEqual($component['type'], 'comment_default');
      $this->assertEqual($component['weight'], 20);
    }
  }
}
