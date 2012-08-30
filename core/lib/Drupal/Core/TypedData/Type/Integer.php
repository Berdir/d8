<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Integer.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The integer data type.
 */
class Integer extends DataWrapperBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var integer
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = isset($value) ? (int) $value : $value;
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
