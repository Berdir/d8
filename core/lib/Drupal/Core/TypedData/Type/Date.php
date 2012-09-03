<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Date.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\WrapperInterface;
use DateTime;
use InvalidArgumentException;

/**
 * The date data type.
 *
 * The plain value of a date is an instance of the DateTime class. For setting
 * the value an instance of the DateTime class, any string supported by
 * DateTime::__construct(), or a timestamp as integer may be passed.
 */
class Date extends WrapperBase implements WrapperInterface {

  /**
   * The data value.
   *
   * @var DateTime
   */
  protected $value;

  /**
   * Implements WrapperInterface::getValue().
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements WrapperInterface::setValue().
   */
  public function setValue($value) {
    if ($value instanceof DateTime || !isset($value)) {
      $this->value = $value;
    }
    elseif (is_integer($value)) {
      // Value is a timestamp.
      $this->value = new DateTime('@' . $value);
    }
    elseif (is_string($value)) {
      $this->value = new DateTime($value);
    }
    else {
      throw new InvalidArgumentException("Invalid date format given.");
    }
  }

  /**
   * Implements WrapperInterface::getString().
   */
  public function getString() {
    return (string) $this->getValue()->format(DateTime::ISO8601);
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
