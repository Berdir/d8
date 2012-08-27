<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Date.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The date data type.
 */
class Date extends DataTypeBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var integer
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::getValue().
   */
  public function getValue() {
    return new \DateTime($this->value);
  }

  /**
   * Implements DataWrapperInterface::getString().
   */
  public function getString() {
    return (string) $this->getValue()->format(DateTime::ISO8601);
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
