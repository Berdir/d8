<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Field.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;
use Drupal\migrate\Row;

/**
 * Drupal 6 field source from database.
 *
 * @PluginId("drupal6_field")
 */
class Field extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('content_node_field', 'cnf')
      ->fields('cnf', array(
        'field_name',
        'type',
        'global_settings',
        'required',
        'multiple',
        'db_storage',
        'module',
        'db_columns',
        'active',
        'locked',
      ));

    $query->orderBy('field_name');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'field_name' => t('Field name'),
      'type' => t('Type (text, integer, ....)'),
      'global_settings' => t('Global settings. Shared with every field instance.'),
      'required' => t('Required'),
      'multiple' => t('Multiple'),
      'db_storage' => t('DB storage'),
      'module' => t('Module'),
      'db_columns' => t('DB Columns'),
      'active' => t('Active'),
      'locked' => t('Locked'),
    );
  }

  function prepareRow(Row $row, $keep = TRUE) {
    //Unserialize data
    $global_settings = unserialize($row->getSourceProperty('global_settings'));
    $db_columns = unserialize($row->getSourceProperty('db_columns'));
    $row->setSourceProperty('global_settings', $global_settings);
    $row->setSourceProperty('db_columns', $db_columns);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('content') && $this->getModuleSchemaVersion('content') >= 6001;
  }

}
