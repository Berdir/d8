<?php

/**
 * @file
 * Definition of Drupal\Core\Property\PropertyTypeInterface.
 */

namespace Drupal\Core\Property;

/**
 * For primitives.
 */
interface PropertyTypeInterface {

  // Validate a given value and make sure it matches it definition.
  // Implement per type validation logic. e.g. support regex for strings.
  public function validate($value, array $definition);
}

