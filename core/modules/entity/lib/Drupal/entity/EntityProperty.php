<?php

/**
 * @file
 * Definition of Drupal\entity\EntityProperty.
 */

namespace Drupal\entity;

/**
 * An entity property.
 *
 * An entity property is a list of property items, which contain only primitive
 * properties or entity references. Note that even single-valued entity
 * properties are represented as list of items, however for easy access to the
 * contained item the entity property delegates get() and set() calls directly
 * to the first item.
 *
 * @see EntityPropertyInterface.
 */
class EntityProperty implements EntityPropertyInterface {

  /**
   * Numerically indexed array of property items, implementing the
   * EntityPropertyItemInterface.
   *
   * @var array
   */
  protected $list = array();

  /**
   * The class to use for the property items.
   *
   * @var string
   */
  protected $class;

  /**
   * The definition of the represented property.
   *
   * @var array
   */
  protected $definition;


  /**
   * @param array $definition
   *   The definition of the entity property.
   * @param array $values
   *   The array of raw values of the entity property.
   * @param string $class
   *   (optional) The class to use for the property items. Must implement
   *   \Drupal\entity\EntityPropertyItemInterface. Defaults to
   *   \Drupal\entity\EntityPropertyItem.
   */
  public function __construct(array $definition, array $values = array(), $class = '\Drupal\entity\EntityPropertyItem') {
    $this->class = $class;
    $this->definition = $definition;
    foreach ($values as $value) {
      $this->list[] = new $this->class($this->definition, $value);
    }
  }

  public function offsetExists($offset) {
    return array_key_exists($offset, $this->list);
  }

  public function offsetUnset($offset) {
    unset($this->list[$offset]);
  }

  public function offsetGet($offset) {
    return $this->list[$offset];
  }

  public function offsetSet($offset, $value) {
    // Support setting the property using the raw value as well.
    // @todo: Needs tests.
    if (!($value instanceof EntityPropertyItemInterface)) {
      $value = new $this->class($this->definition, $value);
    }
    $this->list[$offset] = $value;
  }

  public function getIterator() {
    // @todo: Fix to iterate over the properties, not over the raw values.
    return new ArrayIterator($this->list);
  }

  public function count() {
    return count($this->list);
  }

  /**
   * Delegate.
   */
  public function getProperties() {
    return $this->offsetGet(0)->getProperties();
  }

  /**
   * Delegate.
   */
  public function getPropertyDefinition($name) {
    return $this->offsetGet(0)->getPropertyDefinition($name);
  }

  /**
   * Delegate.
   */
  public function getPropertyDefinitions() {
    return $this->offsetGet(0)->getPropertyDefinitions();
  }

  /**
   * Delegate.
   */
  public function __get($property_name) {
    return $this->offsetGet(0)->__get($property_name);
  }

  /**
   * Delegate.
   */
  public function get($property_name) {
    return $this->offsetGet(0)->get($property_name);
  }

  /**
   * Delegate.
   */
  public function getRawValue($property_name) {
    return $this->offsetGet(0)->getRawValue($property_name);
  }

  /**
   * Delegate.
   */
  public function set($property_name, $value) {
    $this->offsetGet(0)->set($property_name, $value);
  }

  /**
   * Delegate.
   */
  public function __set($property_name, $value) {
    $this->offsetGet(0)->__set($property_name, $value);
  }

  /**
   * Gets the the raw array representation of the entity property.
   *
   * @return array
   *   The raw array representation of the entity property, i.e. an array
   *   containing the raw values of all contained items.
   */
  public function toArray() {
    $values = array();
    foreach ($this->list as $item) {
      $value = array();
      foreach ($item->getPropertyDefinitions() as $name => $definition) {
        $value[$name] = $item->getRawValue($name);
      }
      $values[] = $value;
    }
    return $values;
  }

  public function access($account) {
    // TODO: Implement access() method. Use item access.
  }

  public function validate() {
    // TODO: Implement validate() method.
  }
}