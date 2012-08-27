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
class Decimal extends DataTypeBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var integer
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
