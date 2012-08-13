<?php

/**
 * @file
 * Definition of Drupal\Core\Property\Type\String.
 */

namespace Drupal\Core\Property\Type;
use Drupal\Core\Property\PropertyInterface;

/**
 * The string property type.
 */
class String implements PropertyInterface {

  /**
   * The property definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The property value.
   *
   * @var string
   */
  protected $value;

  /**
   * Implements PropertyInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements PropertyInterface::getType().
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements PropertyInterface::getDefinition().
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements PropertyInterface::getValue().
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements PropertyInterface::setValue().
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Implements PropertyInterface::getString().
   */
  public function getString() {
    return (string) $this->value;
  }

  /**
   * Implements PropertyInterface::validate().
   */
  public function validate($value = NULL) {
    // TODO: Implement validate() method.
  }
}
