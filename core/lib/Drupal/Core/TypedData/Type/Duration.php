<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Duration.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The duration data type.
 */
class Duration extends DataTypeBase implements DataWrapperInterface {

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
