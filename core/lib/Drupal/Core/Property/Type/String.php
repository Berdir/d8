<?php

/**
 * @file
 * Definition of Drupal\Core\Property\Type\String.
 */

namespace Drupal\Core\Property\Type;
use Drupal\Core\Property\PropertyTypeInterface;

/**
 * The string property type.
 */
class String implements PropertyTypeInterface {

  public function validate($value, array $definition) {
    // TODO: Implement validate() method, i.e. apply the 'regex' key of the
    // definition?
  }
}
