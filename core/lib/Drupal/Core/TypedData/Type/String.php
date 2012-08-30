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
class String extends DataWrapperBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var string
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (string) $value : $value;
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
