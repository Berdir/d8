<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTypeEntityFormDsiplay.
 */

namespace Drupal\migrate_drupal\Tests\d6;

class MigrateNodeTypeEntityFormDisplay extends MigrateNodeTypeEntityDisplay {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate node body into entity form display,',
      'description'  => 'Upgrade node body settings to entity.form_display.node.*.default.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Verify expectations.
   */
  protected function verifyExpected($types) {
    foreach ($types as $type) {
      $component = entity_get_form_display('node', $type, $this->viewMode)->getComponent('body');
      $this->assertEqual($component['type'], 'text_textarea_with_summary');
    }
  }

}
