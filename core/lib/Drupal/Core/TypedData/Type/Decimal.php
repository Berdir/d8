<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Decimal.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The decimal data type.
 */
class Decimal extends DataWrapperBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var float
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (float) $value : $value;
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
