<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Duration.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\WrapperInterface;
use DateInterval;
use InvalidArgumentException;

/**
 * The duration data type.
 */
class Duration extends WrapperBase implements WrapperInterface {

  /**
   * The data value.
   *
   * @var \DateInterval
   */
  protected $value;

  /**
   * Implements WrapperInterface::setValue().
   */
  public function setValue($value) {
    if ($value instanceof DateInterval || !isset($value)) {
      $this->value = $value;
    }
    elseif (is_integer($value)) {
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
   * Implements WrapperInterface::getString().
   */
  public function getString() {
    return (string) $this->getValue()->format('%rP%yY%mM%dDT%hH%mM%sS');
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
