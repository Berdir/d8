<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;

/**
 * Handle the importing of a specific configurable field type.
 */
interface MigrateEntityDestinationFieldInterface {

  /**
   * Convert an array of values into an array structure fit for entity_create.
   *
   * @param array $values
   *   The array of values.
   * @return array|NULL
   *   This will be set in the $values array passed to entity_create() as the
   *   value of a configurable field of the type this class handles.
   */
  public function import(array $values = NULL);

}
