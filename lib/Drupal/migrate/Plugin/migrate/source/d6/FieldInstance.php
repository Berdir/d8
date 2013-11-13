<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\FieldInstance.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;

use Drupal\migrate\Row;

/**
 * Drupal 6 field instances source from database.
 *
 * @PluginId("drupal6_fieldinstance")
 */
class FieldInstance extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('content_node_field_instance', 'cnfi')
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
      ));

    $query->orderBy('type_name');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'field_name' => t('The machine name of field.'),
      'type_name' => t('Content type where is used this field.'),
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

  function prepareRow(Row $row, $keep = TRUE) {
    //Unserialize data
    $widget_settings = unserialize($row->getSourceProperty('widget_settings'));
    $display_settings = unserialize($row->getSourceProperty('display_settings'));
    $row->setSourceProperty('widget_settings', $widget_settings);
    $row->setSourceProperty('display_settings', $display_settings);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('content') && $this->getModuleSchemaVersion('content') >= 6001;
  }

}
