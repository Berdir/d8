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
class Boolean extends DataWrapperBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var boolean
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (bool) $value : $value;
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
