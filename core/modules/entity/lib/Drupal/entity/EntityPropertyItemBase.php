<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyItem.
 */

namespace Drupal\entity;
use \Drupal\Core\Property\PropertyTypeContainerInterface;
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

    if (isset($values)) {
      $definitions = $this->getPropertyDefinitions();

      // Clear the values of properties for which no value has been passed.
      foreach (array_diff_key($definitions, $values) as $name => $definition) {
        unset($this->properties[$name]);
      }

      // Set the values.
      foreach ($values as $name => $value) {
        if (!isset($this->properties[$name]) && isset($definitions[$name])) {
          $this->properties[$name] = drupal_get_property($definitions[$name], $value);
        }
        elseif (isset($definitions[$name])) {
          $this->properties[$name]->setValue($value);
        }
        // @todo: Throw an exception else? Invalid value given?
      }
    }
    else {
      $this->properties = array();
    }
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
    // If no property object is there yet, create a new and empty object.
    if (!isset($this->properties[$property_name])) {
      $definition = $this->getPropertyDefinition($property_name);
      $this->properties[$property_name] = drupal_get_property($definition);
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
        $properties[$name] = $this->get($name);
      }
    }
    return $properties;
  }

  /**
   * Implements PropertyContainerInterface::setProperties().
   */
  public function setProperties($properties) {
    $definitions = $this->getPropertyDefinitions();
    foreach ($properties as $name => $property) {
      if (isset($definitions[$name])) {
        $this->properties[$name] = $property;
      }
      // @todo: Throw exception else, invalid properties given?.
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