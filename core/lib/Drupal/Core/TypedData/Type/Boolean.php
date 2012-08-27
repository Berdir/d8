<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Boolean.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The boolean data type.
 */
class Boolean extends DataTypeBase implements DataWrapperInterface {

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
