<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Decimal.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * The decimal data type.
 *
 * The plain value of a decimal is a regular PHP float. For setting the value
 * any PHP variable that casts to a float may be passed.
 */
class Decimal extends TypedData implements TypedDataInterface {

  /**
   * The data value.
   *
   * @var float
   */
  protected $value;

  /**
   * Implements TypedDataInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (float) $value : $value;
  }

  /**
   * Implements TypedDataInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
