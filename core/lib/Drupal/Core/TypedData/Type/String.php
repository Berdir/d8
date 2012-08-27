<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\String.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The string data type.
 */
class String extends DataTypeBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var string
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
