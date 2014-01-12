<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\FieldInstancePerViewMode.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Entity\MigrationInterface;

/**
 * A base class for field instances which all require the same data and fields.
 *
 * @PluginID("drupal6_field_instance_per_view_mode")
 */
class FieldInstancePerViewMode extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function runQuery() {
    $rows = array();
    $result = $this->prepareQuery()->execute();
    while ($field_row = $result->fetchAssoc()) {
      // These are added to every view mode row.
      $field_row['display_settings'] = unserialize($field_row['display_settings']);
      $field_row['widget_settings'] = unserialize($field_row['widget_settings']);
      $bundle = $field_row['type_name'];
      $field_name = $field_row['field_name'];
      foreach ($this->getViewModes() as $view_mode) {
        if (isset($field_row['display_settings'][$view_mode]) && !$field_row['display_settings'][$view_mode]['exclude']) {
          $index = $view_mode . "." . $bundle;
          if (!isset($rows[$index])) {
            $rows[$index]['entity_type'] = 'node';
            $rows[$index]['view_mode'] = $view_mode;
            $rows[$index]['type_name'] = $bundle;
          }

          $rows[$index]['fields'][$field_name]['field_name'] = $field_name;
          $rows[$index]['fields'][$field_name]['type'] = $field_row['type'];
          $rows[$index]['fields'][$field_name]['module'] = $field_row['module'];
          $rows[$index]['fields'][$field_name]['weight'] = $field_row['display_settings']['weight'];
          $rows[$index]['fields'][$field_name]['label'] = $field_row['display_settings']['label']['format'];
          $rows[$index]['fields'][$field_name]['display_settings'] = $field_row['display_settings'][$view_mode];
          $rows[$index]['fields'][$field_name]['widget_settings'] = $field_row['widget_settings'];
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
        'field_name',
        'type_name',
        'weight',
        'label',
        'display_settings',
        'widget_settings',
    ))
    ->fields('cnf', array(
        'type',
        'module',
    ));
    $query->join('content_node_field', 'cnf', 'cnfi.field_name = cnf.field_name');
    $query->orderBy('type_name');

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
