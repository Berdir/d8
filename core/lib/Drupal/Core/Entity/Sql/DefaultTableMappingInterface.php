<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Sql\DefaultTableMappingInterface.
 */

namespace Drupal\Core\Entity\Sql;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Provides an common interface for mapping field columns to SQL tables.
 */
interface DefaultTableMappingInterface extends TableMappingInterface {

  /**
   * Returns a list of dedicated table names for this mapping.
   *
   * TODO Do we really need this or should we return everything in
   *   TableMappingInterface::getTableNames()?
   *
   * @return string[]
   *   An array of table names.
   */
  function getDedicatedTableNames();

  /**
   * Checks whether the given field can be stored in a shared table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   *
   * @return bool
   *   TRUE if the field can be stored in a dedicated table, FALSE otherwise.
   */
  function allowsSharedTableStorage(FieldStorageDefinitionInterface $storage_definition);

  /**
   * Checks whether the given field can be stored in a shared table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   *
   * @return bool
   *   TRUE if the field can be stored in a dedicated table, FALSE otherwise.
   */
  function requiresDedicatedTableStorage(FieldStorageDefinitionInterface $storage_definition);

  /**
   * A list of columns that can not be used as field type columns.
   *
   * @return array
   */
  public function getReservedColumns();

  /**
   * Generates a table name for a field data table.
   *
   * @private Calling this function circumvents the entity system and is
   * strongly discouraged. This function is not considered part of the public
   * API and modules relying on it might break even in minor releases. Only
   * call this function to write a query that \Drupal::entityQuery() does not
   * support. Always call entity_load() before using the data found in the
   * table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   * @param bool $is_deleted
   *   (optional) Whether the table name holding the values of a deleted field
   *   should be returned.
   *
   * @return string
   *   A string containing the generated name for the database table.
   */
  public function getDedicatedDataTableName(FieldStorageDefinitionInterface $storage_definition, $is_deleted = FALSE);

  /**
   * Generates a table name for a field revision archive table.
   *
   * @private Calling this function circumvents the entity system and is
   * strongly discouraged. This function is not considered part of the public
   * API and modules relying on it might break even in minor releases. Only
   * call this function to write a query that \Drupal::entityQuery() does not
   * support. Always call entity_load() before using the data found in the
   * table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   * @param bool $is_deleted
   *   (optional) Whether the table name holding the values of a deleted field
   *   should be returned.
   *
   * @return string
   *   A string containing the generated name for the database table.
   */
  public function getDedicatedRevisionTableName(FieldStorageDefinitionInterface $storage_definition, $is_deleted = FALSE);


  /**
   * Generates a column name for a field data table.
   *
   * @private Calling this function circumvents the entity system and is
   * strongly discouraged. This function is not considered part of the public
   * API and modules relying on it might break even in minor releases. Only
   * call this function to write a query that \Drupal::entityQuery() does not
   * support. Always call entity_load() before using the data found in the
   * table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   * @param string $column
   *   The name of the column.
   *
   * @return string
   *   A string containing a generated column name for a field data table that is
   *   unique among all other fields.
   */
  public function getFieldColumnName(FieldStorageDefinitionInterface $storage_definition, $column);

}
