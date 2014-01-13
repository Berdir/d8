<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTypeEntityDisplayTeaser.
 */

namespace Drupal\migrate_drupal\Tests\d6;

class MigrateNodeTypeEntityDisplayTeaser extends MigrateNodeTypeEntityDisplay {

  protected $migrationId = 'd6_node_type_entity_display_teaser';

  protected $viewMode = 'teaser';

  protected $expectedComponent = array(
    'label' => 'hidden',
    'type' => 'text_summary_or_trimmed',
  );

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

}
