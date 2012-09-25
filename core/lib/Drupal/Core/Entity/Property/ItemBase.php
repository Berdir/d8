<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Property\ItemBase.
 */

namespace Drupal\Core\Entity\Property;
use Drupal\Core\TypedData\Type\TypedData;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\user;
use InvalidArgumentException;
use ArrayIterator;
use IteratorAggregate;

/**
 * An entity property item.
 *
 * Entity property items making use of this base class have to implement
 * ComplexDataInterface::getPropertyDefinitions().
 *
 * @see \Drupal\Core\Entity\Property\ItemInterface
 */
abstract class ItemBase extends TypedData implements IteratorAggregate, ItemInterface {

  /**
   * The array of properties.
   *
   * Property objects are instantiated during object construction and cannot be
   * replaced by others, so computed properties can safely store references on
   * other properties.
   *
   * @var array<TypedDataInterface>
   */
  protected $properties = array();

  /**
   * Implements TypedDataInterface::__construct().
   */
  public function __construct(array $definition) {
    $this->definition = $definition;

    // Initialize all property objects, but postpone the creating of computed
    // properties to a second step. That way computed properties can safely get
    // references on non-computed properties during construction.
    $step2 = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if (empty($definition['computed'])) {
        $context = array('name' => $name, 'parent' => $this);
        $this->properties[$name] = typed_data()->create($definition, NULL, $context);
      }
      else {
        $step2[$name] = $definition;
      }
    }

    foreach ($step2 as $name => $definition) {
      $context = array('name' => $name, 'parent' => $this);
      $this->properties[$name] = typed_data()->create($definition, NULL, $context);
    }
  }

  /**
   * Implements TypedDataInterface::getValue().
   */
  public function getValue() {
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements TypedDataInterface::setValue().
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
    elseif ($values instanceof TypedDataInterface) {
      $values = $values->getValue();
    }

    foreach ($this->properties as $name => $property) {
      $property->setValue(isset($values[$name]) ? $values[$name] : NULL);
    }
    // @todo: Throw an exception for invalid values once conversion is
    // totally completed.
  }

  /**
   * Implements TypedDataInterface::getString().
   */
  public function getString() {
    $strings = array();
    foreach ($this->getProperties() as $property) {
      $strings[] = $property->getString();
    }
    return implode(', ', array_filter($strings));
  }

  /**
   * Implements TypedDataInterface::validate().
   */
  public function validate() {
    // @todo implement
  }

  /**
   * Implements ComplexDataInterface::get().
   */
  public function get($property_name) {
    if (!isset($this->properties[$property_name])) {
      throw new InvalidArgumentException('Property ' . check_plain($property_name) . ' is unknown.');
    }
    return $this->properties[$property_name];
  }

  /**
   * Implements ComplexDataInterface::set().
   */
  public function set($property_name, $value) {
    $this->get($property_name)->setValue($value);
  }

  /**
   * Implements ItemInterface::__get().
   */
  public function __get($name) {
    return $this->get($name)->getValue();
  }

  /**
   * Implements ItemInterface::__set().
   */
  public function __set($name, $value) {
    $this->get($name)->setValue($value);
  }

  /**
   * Implements ItemInterface::__isset().
   */
  public function __isset($name) {
    return isset($this->properties[$name]) && $this->properties[$name]->getValue() !== NULL;
  }

  /**
   * Implements ItemInterface::__unset().
   */
  public function __unset($name) {
    if (isset($this->properties[$name])) {
      $this->properties[$name]->setValue(NULL);
    }
  }

  /**
   * Implements ComplexDataInterface::getProperties().
   */
  public function getProperties($include_computed = FALSE) {
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if ($include_computed || empty($definition['computed'])) {
        $properties[$name] = $this->properties[$name];
      }
    }
    return $properties;
  }

  /**
   * Implements ComplexDataInterface::getPropertyValues().
   */
  public function getPropertyValues() {
    return $this->getValue();
  }

  /**
   * Implements ComplexDataInterface::setPropertyValues().
   */
  public function setPropertyValues($values) {
    foreach ($values as $name => $value) {
      $this->get($name)->setValue($value);
    }
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new ArrayIterator($this->getProperties());
  }

  /**
   * Implements ComplexDataInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements ComplexDataInterface::isEmpty().
   */
  public function isEmpty() {
    foreach ($this->getProperties() as $property) {
      if ($property->getValue() !== NULL) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Implements a deep clone.
   */
  public function __clone() {
    foreach ($this->properties as $name => $property) {
      $this->properties[$name] = clone $property;
    }
  }
}