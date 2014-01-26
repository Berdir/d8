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
 * @PluginID("drupal6_field_instance_per_form_display")
 */
class FieldInstancePerFormDisplay extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function runQuery() {
    $rows = array();
    $result = $this->prepareQuery()->execute();
    while ($field_row = $result->fetchAssoc()) {
      $field_row['display_settings'] = unserialize($field_row['display_settings']);
      $field_row['widget_settings'] = unserialize($field_row['widget_settings']);
      $bundle = $field_row['type_name'];
      $field_name = $field_row['field_name'];

      $index = "$bundle.$field_name";
      $rows[$index]['type_name'] = $bundle;
      $rows[$index]['widget_active'] = (bool) $field_row['widget_active'];
      $rows[$index]['field_name'] = $field_name;
      $rows[$index]['type'] = $field_row['type'];
      $rows[$index]['module'] = $field_row['module'];
      $rows[$index]['weight'] = $field_row['display_settings']['weight'];
      $rows[$index]['widget_type'] = $field_row['widget_type'];
      $rows[$index]['widget_settings'] = $field_row['widget_settings'];
    }

    return new \ArrayIterator($rows);
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
        'widget_type',
        'widget_settings',
        'display_settings',
        'description',
        'widget_module',
        'widget_active',
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
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['type_name']['type'] = 'string';
    $ids['field_name']['type'] = 'string';
    return $ids;
  }

}
