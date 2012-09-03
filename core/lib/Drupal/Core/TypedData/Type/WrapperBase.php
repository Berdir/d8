<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\WrapperBase.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\WrapperInterface;

/**
 * The abstract base class for data types.
 *
 * Classes deriving from this base class have to declare $value
 * or override getValue() or setValue().
 */
abstract class WrapperBase implements WrapperInterface {

  /**
   * The data definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * Implements WrapperInterface::__construct().
   *
   * @param array $definition
   *
   * @param mixed $value;
   *
   * @param array $context;
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements WrapperInterface::getType().
   *
   * @return string
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements WrapperInterface::getDefinition().
   *
   * @return array
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements WrapperInterface::getValue().
   *
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements WrapperInterface::setValue().
   *
   * @param mixed $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Implements WrapperInterface::getString().
   *
   * @return string
   */
  public function getString() {
    return (string) $this->getValue();
  }
}
