<?php

/**
 * @file
 * Definition of Drupal\Core\Data\Type\String.
 */

namespace Drupal\Core\Data\Type;
use Drupal\Core\Data\DataItemInterface;

/**
 * The string property type.
 */
class String implements DataItemInterface {

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
   * Implements DataItemInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements DataItemInterface::getType().
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements DataItemInterface::getDefinition().
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements DataItemInterface::getValue().
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements DataItemInterface::setValue().
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Implements DataItemInterface::getString().
   */
  public function getString() {
    return (string) $this->value;
  }

  /**
   * Implements DataItemInterface::validate().
   */
  public function validate($value = NULL) {
    // TODO: Implement validate() method.
  }
}
