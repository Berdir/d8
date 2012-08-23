<?php

/**
 * @file
 * Definition of Drupal\entity\Property\EntityPropertyItemBase.
 */

namespace Drupal\entity\Property;
use \Drupal\Core\TypedData\DataWrapperInterface;
use \Drupal\Core\TypedData\DataContainerInterface;

/**
 * An entity property item.
 *
 * Entity property items making use of this base class have to implement the
 * DataContainerInterface::getPropertyDefinitions().
 *
 * @see EntityPropertyItemInterface
 */
abstract class EntityPropertyItemBase implements EntityPropertyItemInterface {

  /**
   * The array of properties.
   *
   * Property objects are instantiated during object construction and cannot be
   * replaced by others, so computed properties can safely store references on
   * other properties.
   *
   * @var array<DataWrapperInterface>
   */
  protected $properties = array();

  /**
   * The definition of the entity property.
   *
   * @var array
   */
  protected $definition;


  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;

    // Initialize all property objects, but postpone the creating of computed
    // properties to a second step. That way computed properties can safely get
    // references on non-computed properties during construction.
    $step2 = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if (empty($definition['computed'])) {
        $context = array('name' => $name, 'parent' => $this);
        $this->properties[$name] = drupal_get_property($definition, NULL, $context);
      }
      else {
        $step2[$name] = $definition;
      }
    }

    foreach ($step2 as $name => $definition) {
      $context = array('name' => $name, 'parent' => $this);
      $this->properties[$name] = drupal_get_property($definition, NULL, $context);
    }

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
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements DataWrapperInterface::setValue().
   *
   * @param array $values
   *   An array of property values.
   */
  public function setValue($values) {
    // Treat the values as property value of the first property, if no array is
    // given and we only have one property.
    if (!is_array($values) && count($this->properties) == 1) {
      $keys = array_keys($this->properties);
      $values = array($keys[0] => $values);
    }
    // Support passing in property objects as value.
    elseif ($values instanceof DataWrapperInterface) {
      $values = $values->getValue();
    }

    foreach ($this->properties as $name => $property) {
      $property->setValue(isset($values[$name]) ? $values[$name] : NULL);
    }
    // @todo: Throw an exception for invalid values once conversion is
    // totally completed.
  }

  /**
   * Implements DataWrapperInterface::getString().
   */
  public function getString() {
    $strings = array();
    foreach ($this->getProperties() as $property) {
      $strings[] = $property->getString();
    }
    return implode(', ', array_filter($strings));
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate($value = NULL) {
    // @todo implement
  }

  /**
   * Gets a property.
   */
  public function get($property_name) {
    if (!isset($this->properties[$property_name])) {
      throw new \InvalidArgumentException('Property ' . check_plain($property_name) . ' is unknown.');
    }
    return $this->properties[$property_name];
  }

  /**
   * Implements EntityPropertyItemInterface::__get().
   */
  public function __get($name) {
    return $this->get($name)->getValue();
  }

  /**
   * Implements EntityPropertyItemInterface::__set().
   */
  public function __set($name, $value) {
    $this->get($name)->setValue($value);
  }

  /**
   * Implements DataContainerInterface::getProperties().
   */
  public function getProperties() {
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if (empty($definition['computed'])) {
        $properties[$name] = $this->properties[$name];
      }
    }
    return $properties;
  }

  /**
   * Implements DataContainerInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($properties as $name => $property) {
      if (isset($this->properties[$name])) {
        // Copy the value to our property object.
        $value = $property instanceof DataWrapperInterface ? $property->getValue() : $property;
        $this->properties[$name]->setValue($value);
      }
      else {
        throw new \InvalidArgumentException('Property ' . check_plain($name) . ' is unknown.');
      }
    }
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new \ArrayIterator($this->getProperties());
  }

  /**
   * Implements DataContainerInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements DataContainerInterface::toArray().
   */
  public function toArray() {
    return $this->getValue();
  }

  /**
   * Implements a deep clone.
   */
  public function __clone() {
    foreach ($this->properties as $name => $property) {
      $this->properties[$name] = clone $property;
    }
  }

  public function access($account = NULL) {
    // @todo implement
  }
}