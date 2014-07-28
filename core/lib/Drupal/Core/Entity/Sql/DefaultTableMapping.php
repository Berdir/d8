<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Sql\DefaultTableMapping.
 */

namespace Drupal\Core\Entity\Sql;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines a default table mapping class.
 */
class DefaultTableMapping implements DefaultTableMappingInterface {

  /**
   * A list of field storage definitions that are available for this mapping.
   *
   * @var \Drupal\Core\Field\FieldStorageDefinitionInterface[]
   */
  protected $fieldStorageDefinitions = array();

  /**
   * A list of base field definitions that are available for this mapping.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface[]
   */
  protected $baseFieldDefinitions = array();

  /**
   * A list of field names per table.
   *
   * This corresponds to the return value of
   * TableMappingInterface::getFieldNames() except that this variable is
   * additionally keyed by table name.
   *
   * @var array[]
   */
  protected $fieldNames = array();

  /**
   * A list of database columns which store denormalized data per table.
   *
   * This corresponds to the return value of
   * TableMappingInterface::getExtraColumns() except that this variable is
   * additionally keyed by table name.
   *
   * @var array[]
   */
  protected $extraColumns = array();

  /**
   * A mapping of column names per field name.
   *
   * This corresponds to the return value of
   * TableMappingInterface::getColumnNames() except that this variable is
   * additionally keyed by field name.
   *
   * This data is derived from static::$storageDefinitions, but is stored
   * separately to avoid repeated processing.
   *
   * @var array[]
   */
  protected $columnMapping = array();

  /**
   * A list of all database columns per table.
   *
   * This corresponds to the return value of
   * TableMappingInterface::getAllColumns() except that this variable is
   * additionally keyed by table name.
   *
   * This data is derived from static::$storageDefinitions, static::$fieldNames,
   * and static::$extraColumns, but is stored separately to avoid repeated
   * processing.
   *
   * @var array[]
   */
  protected $allColumns = array();

  /**
   * Constructs a DefaultTableMapping.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface[] $storage_definitions
   *   A list of field storage definitions that should be available for the
   *   field columns of this table mapping.
   * @param \Drupal\Core\Field\FieldDefinitionInterface[] $base_field_definitions
   *   A list of base field definitions that should be available for the field
   *   columns of this table mapping.
   */
  public function __construct(array $storage_definitions, array $base_field_definitions) {
    $this->fieldStorageDefinitions = $storage_definitions;
    $this->baseFieldDefinitions = $base_field_definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableNames() {
    return array_unique(array_merge(array_keys($this->fieldNames), array_keys($this->extraColumns)));
  }

  /**
   * {@inheritdoc}
   */
  public function getAllColumns($table_name) {
    if (!isset($this->allColumns[$table_name])) {
      $this->allColumns[$table_name] = array();

      foreach ($this->getFieldNames($table_name) as $field_name) {
        $this->allColumns[$table_name] = array_merge($this->allColumns[$table_name], array_values($this->getColumnNames($field_name)));
      }

      // There is just one field for each dedicated storage table, thus
      // $field_name can only refer to it.
      if (isset($field_name) && $this->requiresDedicatedTableStorage($this->fieldStorageDefinitions[$field_name])) {
        $this->allColumns[$table_name] = array_merge($this->getExtraColumns($table_name), $this->allColumns[$table_name]);
      }
      else {
        $this->allColumns[$table_name] = array_merge($this->allColumns[$table_name], $this->getExtraColumns($table_name));
      }
    }
    return $this->allColumns[$table_name];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames($table_name) {
    if (isset($this->fieldNames[$table_name])) {
      return $this->fieldNames[$table_name];
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getColumnNames($field_name) {
    if (!isset($this->columnMapping[$field_name])) {
      $column_names = array_keys($this->fieldStorageDefinitions[$field_name]->getColumns());
      if (count($column_names) == 1) {
        $this->columnMapping[$field_name] = array(reset($column_names) => $field_name);
      }
      else {
        $this->columnMapping[$field_name] = array();
        foreach ($column_names as $column_name) {
          $this->columnMapping[$field_name][$column_name] = $field_name . '__' . $column_name;
        }
      }
    }
    return $this->columnMapping[$field_name];
  }

  /**
   * Adds field columns for a table to the table mapping.
   *
   * @param string $table_name
   *   The name of the table to add the field column for.
   * @param string[] $field_names
   *   A list of field names to add the columns for.
   *
   * @return $this
   */
  public function setFieldNames($table_name, array $field_names) {
    $this->fieldNames[$table_name] = $field_names;
    // Force the re-computation of the column list.
    unset($this->allColumns[$table_name]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtraColumns($table_name) {
    if (isset($this->extraColumns[$table_name])) {
      return $this->extraColumns[$table_name];
    }
    return array();
  }

  /**
   * Adds a extra columns for a table to the table mapping.
   *
   * @param string $table_name
   *   The name of table to add the extra columns for.
   * @param string[] $column_names
   *   The list of column names.
   *
   * @return $this
   */
  public function setExtraColumns($table_name, array $column_names) {
    $this->extraColumns[$table_name] = $column_names;
    // Force the re-computation of the column list.
    unset($this->allColumns[$table_name]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  function allowsSharedTableStorage(FieldStorageDefinitionInterface $storage_definition) {
     return !$storage_definition->hasCustomStorage() && isset($this->baseFieldDefinitions[$storage_definition->getName()]) && !$storage_definition->isMultiple();
  }

  /**
   * {@inheritdoc}
   */
  function requiresDedicatedTableStorage(FieldStorageDefinitionInterface $storage_definition) {
    return !$storage_definition->hasCustomStorage() && !$this->allowsSharedTableStorage($storage_definition);
  }

  /**
   * {@inheritdoc}
   */
  function getDedicatedTableNames() {
    $table_mapping = $this;
    $definitions = array_filter($this->fieldStorageDefinitions, function($definition) use ($table_mapping) { return $table_mapping->requiresDedicatedTableStorage($definition); });
    $data_tables = array_map(function($definition) use ($table_mapping) { return $table_mapping->getDedicatedDataTableName($definition); }, $definitions);
    $revision_tables = array_map(function($definition) use ($table_mapping) { return $table_mapping->getDedicatedRevisionTableName($definition); }, $definitions);
    $dedicated_tables = array_merge(array_values($data_tables), array_values($revision_tables));
    return $dedicated_tables;
  }

  /**
   * {@inheritdoc}
   */
  public function getReservedColumns() {
    return array('deleted');
  }

  /**
   * {@inheritdoc}
   */
  public function getDedicatedDataTableName(FieldStorageDefinitionInterface $storage_definition, $is_deleted = FALSE) {
    if ($is_deleted) {
      // When a field is a deleted, the table is renamed to
      // {field_deleted_data_FIELD_UUID}. To make sure we don't end up with
      // table names longer than 64 characters, we hash the unique storage
      // identifier and return the first 10 characters so we end up with a short
      // unique ID.
      return "field_deleted_data_" . substr(hash('sha256', $storage_definition->getUniqueStorageIdentifier()), 0, 10);
    }
    else {
      return $this->generateFieldTableName($storage_definition, FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDedicatedRevisionTableName(FieldStorageDefinitionInterface $storage_definition, $is_deleted = FALSE) {
    if ($is_deleted) {
      // When a field is a deleted, the table is renamed to
      // {field_deleted_revision_FIELD_UUID}. To make sure we don't end up with
      // table names longer than 64 characters, we hash the unique storage
      // identifier and return the first 10 characters so we end up with a short
      // unique ID.
      return "field_deleted_revision_" . substr(hash('sha256', $storage_definition->getUniqueStorageIdentifier()), 0, 10);
    }
    else {
      return $this->generateFieldTableName($storage_definition, TRUE);
    }
  }

  /**
   * Generates a safe and unambiguous field table name.
   *
   * The method accounts for a maximum table name length of 64 characters, and
   * takes care of disambiguation.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   * @param bool $revision
   *   TRUE for revision table, FALSE otherwise.
   *
   * @return string
   *   The final table name.
   */
  protected function generateFieldTableName(FieldStorageDefinitionInterface $storage_definition, $revision) {
    $separator = $revision ? '_revision__' : '__';
    $table_name = $storage_definition->getTargetEntityTypeId() . $separator . $storage_definition->getName();
    // Limit the string to 48 characters, keeping a 16 characters margin for db
    // prefixes.
    if (strlen($table_name) > 48) {
      // Use a shorter separator, a truncated entity_type, and a hash of the
      // field UUID.
      $separator = $revision ? '_r__' : '__';
      // Truncate to the same length for the current and revision tables.
      $entity_type = substr($storage_definition->getTargetEntityTypeId(), 0, 34);
      $field_hash = substr(hash('sha256', $storage_definition->getUniqueStorageIdentifier()), 0, 10);
      $table_name = $entity_type . $separator . $field_hash;
    }
    return $table_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldColumnName(FieldStorageDefinitionInterface $storage_definition, $column) {
    return in_array($column, $this->getReservedColumns()) ? $column : $storage_definition->getName() . '_' . $column;
  }

}
