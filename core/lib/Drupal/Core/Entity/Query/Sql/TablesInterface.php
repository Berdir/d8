<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Query\Sql\TablesInterface.
 */

namespace Drupal\Core\Entity\Query\Sql;

/**
 * Adds tables and fields to the SQL entity query.
 */
interface TablesInterface {

  /**
   * Adds a field to a database query.
   *
   * @param array $field_definition
   *   An array with two keys:
   *   - field: If it contains a dot, then field name dot field column. If it
   *    doesn't then entity property name. The function will set the binary
   *    key in the array to TRUE if the field is a binary field.
   *   - langcode: The language code the field values are to be shown in.
   * @param string $type
   *   Join type, can either be INNER or LEFT.
   *
   * @throws \Drupal\Core\Entity\Query\QueryException
   *   If $field specifies an invalid relationship.
   *
   * @return string
   *   The return value is a string containing the alias of the table, a dot
   *   and the appropriate SQL column as passed in. This allows the direct use
   *   of this in a query for a condition or sort.
   */
  public function addField(&$field_definition, $type);

}
