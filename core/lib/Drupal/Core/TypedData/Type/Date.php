<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Date.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;
use DateTime;
use InvalidArgumentException;

/**
 * The date data type.
 */
class Date extends DataWrapperBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var DateTime
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::getValue().
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements DataWrapperInterface::setValue().
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
