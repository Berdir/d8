<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\TypedData.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * The abstract base class for typed data.
 *
 * Classes deriving from this base class have to declare $value
 * or override getValue() or setValue().
 */
abstract class TypedData implements TypedDataInterface {

  /**
   * The data definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * Implements TypedDataInterface::__construct().
   */
  public function __construct(array $definition) {
    $this->definition = $definition;
  }

  /**
   * Implements TypedDataInterface::setContext().
   */
  public function setContext(array $context) {
    // No need to keep context by default.
  }

  /**
   * Implements TypedDataInterface::getType().
   *
   * @return string
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements TypedDataInterface::getDefinition().
   *
   * @return array
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements TypedDataInterface::getValue().
   *
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements TypedDataInterface::setValue().
   *
   * @param mixed $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Implements TypedDataInterface::getString().
   *
   * @return string
   */
  public function getString() {
    return (string) $this->getValue();
  }
}
