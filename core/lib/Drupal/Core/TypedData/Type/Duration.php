<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Duration.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\TypedDataInterface;
use DateInterval;
use InvalidArgumentException;

/**
 * The duration data type.
 *
 * The plain value of a duration is an instance of the DateInterval class. For
 * setting the value an instance of the DateInterval class, a ISO8601 string as
 * supported by DateInterval::__construct, or an integer in seconds may be
 * passed.
 */
class Duration extends TypedData implements TypedDataInterface {

  /**
   * The data value.
   *
   * @var \DateInterval
   */
  protected $value;

  /**
   * Implements TypedDataInterface::setValue().
   */
  public function setValue($value) {
    if ($value instanceof DateInterval || !isset($value)) {
      $this->value = $value;
    }
    elseif (is_numeric($value)) {
      // Value is a time span in seconds.
      $this->value = new DateInterval('PT' . $value . 'S');
    }
    elseif (is_string($value)) {
      // @todo: Add support for negative intervals on top of the DateInterval
      // constructor.
      $this->value = new DateInterval($value);
    }
    else {
      throw new InvalidArgumentException("Invalid duration format given.");
    }
  }

  /**
   * Implements TypedDataInterface::getString().
   */
  public function getString() {
    // Generate an ISO 8601 formatted string as supported by
    // DateInterval::__construct() and setValue().
    return (string) $this->getValue()->format('%rP%yY%mM%dDT%hH%mM%sS');
  }

  /**
   * Implements TypedDataInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
