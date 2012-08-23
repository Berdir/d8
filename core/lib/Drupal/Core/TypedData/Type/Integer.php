<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Integer.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The string property type.
 */
class Integer implements DataWrapperInterface {

  /**
   * The property definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The property value.
   *
   * @var integer
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
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
    return $this->value;
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
    return (string) $this->value;
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate($value = NULL) {
    // TODO: Implement validate() method.
  }
}
