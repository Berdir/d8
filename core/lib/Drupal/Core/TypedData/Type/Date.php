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
class Date implements DataWrapperInterface {

  /**
   * The data definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The data value.
   *
   * @var integer
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements DataWrapperInterface::getType().
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements DataWrapperInterface::getDefinition().
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements DataWrapperInterface::getValue().
   */
  public function getValue() {
    return new \DateTime($this->value);
  }

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->value = $value;
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
