<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateCommentVariableDisplayBase.
 */


namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Base class for Drupal 6 comment variables to Drupal 8 entity display tests.
 */
class MigrateCommentVariableDisplayBase extends MigrateDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  static $modules = array('comment', 'node');

  /**
   * The database dumps used.
   *
   * @var array
   */
  protected $dumps;

  /**
   * The node types being tested.
   *
   * @var array
   */
  protected $types = array('page', 'story');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    entity_create('field_config', array(
      'entity_type' => 'node',
      'name' => 'comment',
      'type' => 'comment',
      'translatable' => '0',
    ))->save();
    foreach ($this->types as $type) {
      entity_create('node_type', array('type' => $type))->save();
      entity_create('field_instance_config', array(
        'label' => 'Comment settings',
        'description' => '',
        'field_name' => 'comment',
        'entity_type' => 'node',
        'bundle' => $type,
        'required' => 1,
      ))->save();
    }
    $this->dumps = array(
      drupal_get_path('module', 'migrate_drupal') . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6CommentVariable.php',
    );
    $id_mappings = array(
      'd6_comment_field_instance' => array(
        array(array('page'), array('node', 'comment', 'page')),
      ),
    );
    $this->prepareIdMappings($id_mappings);
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', static::MIGRATION);
    $this->prepare($migration, $this->dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

  }

}
