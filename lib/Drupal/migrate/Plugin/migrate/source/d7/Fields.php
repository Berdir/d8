<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d7\Fields.
 */

namespace Drupal\migrate\Plugin\migrate\source\d7;

use Drupal\Core\Database\Connection;

class Fields {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var string
   */
  protected $entityType;

  /**
   * @var string
   */
  protected $bundle;

  /**
   * @var array
   */
  protected $sourceFieldInfo = array();

  public function __construct(Connection $database, $entity_type, $bundle = '') {
    $this->database = $database;
    $this->entityType = $entity_type;
    $this->bundle = $bundle ?: $entity_type;
  }

  /**
   * Retrieve info on all fields attached to the given entity type and bundle.
   *
   * @param $entity_type
   * @param $bundle
   * @param $include_body
   */
  protected function getSourceFieldInfo() {
    if (empty($this->sourceFieldInfo)) {
      migrate_instrument_start('DrupalVersion7::sourceFieldInfo');

      // Get each field attached to this type.
      if ($this->database->schema()->tableExists('field_config_instance')) {
        $query = $this->database->select('field_config_instance', 'i')
                 ->fields('i', array('data'))
                 ->condition('entity_type', $this->entityType)
                 ->condition('bundle', $this->bundle)
                 ->condition('i.deleted', 0);
        $query->innerJoin('field_config', 'f', 'i.field_name = f.field_name');
        $query->fields('f', array('field_name', 'type', 'module'));
        $result = $query->execute();
        foreach ($result as $row) {
          $data = !empty($row->data) ? unserialize($row->data) : array();
          // Although a format column is present for text fields with text
          // filtering disabled, we want to skip it
          if (substr($row->type, 0, 4) == 'text' &&
              $data['settings']['text_processing'] == 0) {
            $skip_format = TRUE;
          }
          else {
            $skip_format = FALSE;
          }
          $this->sourceFieldInfo[trim($row->field_name)] = array(
            'label' => $data['label'],
            'type' => $row->type,
            'columns' => $this->getSourceFieldColumns($row->field_name, $skip_format),
          );
        }
      }
      migrate_instrument_stop('DrupalVersion7::sourceFieldInfo');
    }
    return $this->sourceFieldInfo;
  }

  /**
   * Pick up the list of database columns used for a given field. Unlike D6 CCK,
   * we don't have a definitive list in the configuration tables, so we query
   * the field table.
   *
   * @param $field_name
   *
   * @return array
   */
  public function getSourceFieldColumns($field_name) {
    $table = 'field_data_' . $field_name;
    $row = $this->database->select($table, 'r')
                     ->fields('r')
                     ->range(0, 1)
                     ->execute()
                     ->fetchAssoc();
    $columns = array();
    if (!empty($row)) {
      $prefix = $field_name . '_';
      $prefix_len = strlen($prefix);
      foreach ($row as $column_name => $value) {
        if ($prefix == substr($column_name, 0, $prefix_len)) {
          $suffix = substr($column_name, $prefix_len);
          $display_name = $field_name . ':' . $suffix;
          $columns[$display_name] = $column_name;
        }
      }
    }
    return $columns;
  }

  /**
   * Populate a migration's source row object with field values.
   *
   * @param $row
   * @param $entity_id
   * @param $include_body
   */
  public function getSourceValue($row, $entity_id) {
    $source_info = $this->getSourceFieldInfo();
    // Load up field data for dynamically mapped fields
    foreach ($source_info as $field_name => $field_info) {
      // Find the data in field_data_$field_name.
      $table = "field_data_$field_name";
      $result = $this->database->select($table, 'f')
                ->fields('f')
                ->condition('entity_type', $this->entityType)
                ->condition('bundle', $this->bundle)
                ->condition('entity_id', $entity_id)
                ->orderBy('delta')
                ->execute();
      foreach ($result as $field_row) {
        $i = 0;
        // We assume the first column is the "primary" value of the field, and
        // assign the field name rather than the column name for it.
        foreach ($field_name['columns'] as $display_name => $column_name) {
          if ($i++ == 0) {
            $index = $field_name;
          }
          else {
            $index = $display_name;
          }
          if (isset($row->$index) && !is_array($row->$index)) {
            $row->$index = array($row->$index);
          }
          $row->{$index}[] = $field_row->$column_name;
        }
      }
    }
  }

}
