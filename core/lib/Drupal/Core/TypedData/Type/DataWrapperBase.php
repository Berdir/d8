<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\DataWrapperBase.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The abstract base class for data types.
 *
 * Classes deriving from this base class have to declare $value
 * or override getValue() or setValue().
 */
abstract class DataWrapperBase implements DataWrapperInterface {

  /**
   * The data definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * Implements DataWrapperInterface::__construct().
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
   * Implements DataWrapperInterface::getType().
   *
   * @return string
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements DataWrapperInterface::getDefinition().
   *
   * @return array
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements DataWrapperInterface::getValue().
   *
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements DataWrapperInterface::setValue().
   *
   * @param mixed $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Implements DataWrapperInterface::getString().
   *
   * @return string
   */
  public function getString() {
    return (string) $this->getValue();
  }
}
