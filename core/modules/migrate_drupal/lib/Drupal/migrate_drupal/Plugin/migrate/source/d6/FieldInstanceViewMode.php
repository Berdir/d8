<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\FieldInstanceViewMode.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;

/**
 * A base class for field instances which all require the same data and fields.
 *
 * @PluginID("drupal6_field_instance_view_mode")
 */
class FieldInstanceViewMode extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function runQuery() {
    // @TODO add tags https://drupal.org/node/2165287
    $rows = array();
    $result = $this->query()->execute();
    while ($field_row = $result->fetchAssoc()) {
      $field_row['display_settings'] = unserialize($field_row['display_settings']);
      foreach ($this->getViewModes() as $view_mode) {
        if (isset($field_row['display_settings'][$view_mode]) && !$field_row['display_settings'][$view_mode]['exclude']) {
          if (!isset($rows[$view_mode])) {
            $rows[$view_mode]['entity_type'] = 'node';
            $rows[$view_mode]['view_mode'] = $view_mode;
          }
        }
      }
    }

    return new \ArrayIterator($rows);
  }


  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->runQuery());
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('content_node_field_instance', 'cnfi')
      ->fields('cnfi', array(
        'display_settings',
      ));

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'field_name' => t('The machine name of field.'),
      'type_name' => t('Content type where this field is used.'),
      'weight' => t('Weight.'),
      'label' => t('A name to show.'),
      'widget_type' => t('Widget type.'),
      'widget_settings' => t('Serialize data with widget settings.'),
      'display_settings' => t('Serialize data with display settings.'),
      'description' => t('A description of field.'),
      'widget_module' => t('Module that implements widget.'),
      'widget_active' => t('Status of widget'),
    );
  }

  /**
   * Get a list of D6 view modes.
   *
   * Drupal 6 supported the following view modes.
   * NODE_BUILD_NORMAL = 0
   * NODE_BUILD_PREVIEW = 1
   * NODE_BUILD_SEARCH_INDEX = 2
   * NODE_BUILD_SEARCH_RESULT = 3
   * NODE_BUILD_RSS = 4
   * NODE_BUILD_PRINT = 5
   * teaser
   * full
   *
   * @return array
   *   The view mode names.
   */
  public function getViewModes() {
    return array(
      0,
      1,
      2,
      3,
      4,
      5,
      'teaser',
      'full',
    );
  }

}
