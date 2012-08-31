<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Uri.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\WrapperInterface;

/**
 * The URI data type.
 */
class Uri extends WrapperBase implements WrapperInterface {

  /**
   * The data value.
   *
   * @var string
   */
  protected $value;

  /**
   * Implements WrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (string) $value : $value;
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
