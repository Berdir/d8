<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateCommentVariableDisplayBase.
 */


namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

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
  protected $types;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    entity_create('field_entity', array(
      'entity_type' => 'node',
      'name' => 'comment',
      'type' => 'comment',
      'translatable' => '0',
    ))->save();
    $this->types = array('page', 'story');
    foreach ($this->types as $type) {
      entity_create('node_type', array('type' => $type))->save();
      entity_create('field_instance', array(
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

    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_field_instance' => array(
        array(array('fieldname', 'page'), array('fieldname', 'page')),
      ),
    );
    $this->prepareIdMappings($id_mappings);
  }

}
