<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyItem.
 */

namespace Drupal\entity;
use \Drupal\Core\Property\PropertyInterface;
use \Drupal\Core\Property\PropertyContainerInterface;

/**
 * An entity property item.
 *
 * Entity property items making use of this base class have to implement the
 * PropertyContainerInterface::getPropertyDefinitions().
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
   * @var array<PropertyInterface>
   */
  protected $properties = array();

  /**
   * The definition of the entity property.
   *
   * @var array
   */
  protected $definition;


  /**
   * Implements PropertyInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL) {
    $this->definition = $definition;

    // Initialize all property objects.
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      $this->properties[$name] = drupal_get_property($definition);
    }

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
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements PropertyInterface::setValue().
   *
   * @param array $values
   *   An array of property values.
   */
  public function setValue($values) {
    foreach ($this->properties as $name => $property) {
      $property->setValue(isset($values[$name]) ? $values[$name] : NULL);
    }
    // @todo: Throw an exception for invalid values once conversion is
    // totally completed.
  }

  /**
   * Implements PropertyInterface::getString().
   */
  public function getString() {
    $strings = array();
    foreach ($this->getProperties() as $property) {
      $strings[] = $property->getString();
    }
    return implode(', ', array_filter($strings));
  }

  /**
   * Implements PropertyInterface::validate().
   */
  public function validate($value = NULL) {
    // @todo implement
  }

  /**
   * Implements PropertyInterface::get().
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
   * Implements PropertyContainerInterface::getProperties().
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
   * Implements PropertyContainerInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($properties as $name => $property) {
      if (isset($this->properties[$name])) {
        // Copy the value to our property object.
        $value = $property instanceof PropertyInterface ? $property->getValue() : $property;
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
   * Implements PropertyContainerInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements PropertyContainerInterface::toArray().
   */
  public function toArray() {
    return $this->getValue();
  }

  public function access($account = NULL) {
    // @todo implement
  }
}