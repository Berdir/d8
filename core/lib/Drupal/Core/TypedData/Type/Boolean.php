<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Boolean.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\WrapperInterface;

/**
 * The boolean data type.
 */
class Boolean extends WrapperBase implements WrapperInterface {

  /**
   * The data value.
   *
   * @var boolean
   */
  protected $value;

  /**
   * Implements WrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (bool) $value : $value;
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
