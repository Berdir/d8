<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Date.
 */

namespace Drupal\Core\TypedData\Type;

use DateTime;
use Drupal\Core\TypedData\TypedDataInterface;
use InvalidArgumentException;

/**
 * The date data type.
 *
 * The plain value of a date is an instance of the DateTime class. For setting
 * the value any value supported by the __construct() of the DateTime
 * class will work, including a DateTime object, a timestamp, a string
 * date, or an array of date parts.
 */
class Date extends TypedData implements TypedDataInterface {

  /**
   * The data value.
   *
   * @var DateTime
   */
  protected $value;

  /**
   * Implements TypedDataInterface::setValue().
   */
  public function setValue($value) {

    if ($value instanceof DateTime || !isset($value)) {
      $this->value = $value;
    }
    // Treat integer values as timestamps, even if supplied as PHP string.
    elseif ((string) (int) $value === (string) $value) {
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
   * Implements TypedDataInterface::getString().
   */
  public function getString() {
    return (string) $this->getValue()->format(DateTime::ISO8601);
  }

  /**
   * Implements TypedDataInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
