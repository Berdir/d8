<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTypeEntityDisplayTeaser.
 */

namespace Drupal\migrate_drupal\Tests\d6;

/**
 * Tests the Drupal 6 teaser settings to Drupal 8 entity display migration.
 */
class MigrateNodeTypeEntityDisplayTeaserTest extends MigrateNodeTypeEntityDisplayTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node');

  /**
   * The id of the migration being tested.
   *
   * @var string
   */
  protected $migrationId = 'd6_node_type_entity_display_teaser';

  /**
   * The view mode.
   *
   * @var string
   */
  protected $viewMode = 'teaser';

  /**
   * The expected contents of the component.
   *
   * @var array
   */
  protected $expectedComponent = array(
    'label' => 'hidden',
    'type' => 'text_summary_or_trimmed',
  );

  /**
   * The expected settings in the component.
   *
   * @var array
   */
  protected $expectedSettings = array(
    'trim_length' => 456,
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate node body into entity display,',
      'description'  => 'Upgrade node body settings to entity.display.node.*.default.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function testNodeTypeEntityDisplay() {
    parent::testNodeTypeEntityDisplay();
  }

}
