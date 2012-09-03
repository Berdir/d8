<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Integer.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\WrapperInterface;

/**
 * The integer data type.
 */
class Integer extends WrapperBase implements WrapperInterface {

  /**
   * The data value.
   *
   * @var integer
   */
  protected $value;

  /**
   * Implements WrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (int) $value : $value;
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
