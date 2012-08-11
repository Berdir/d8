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
 * contained item the entity property delegates __get() and __set() calls
 * directly to the first item.
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
    foreach ($this->list as $delta => $item) {
      // @todo: Filter out empty items and add an isEmpty() method to them.
      $values[$delta] = $item->getValue();
    }
    return $values;
  }

  /**
   * Implements PropertyInterface::setValue().
   *
   * @param array $values
   *   An array of values of the property items.
   */
  public function setValue($values) {
    if (isset($values)) {
      // Clear the values of properties for which no value has been passed.
      foreach (array_diff_key($this->list, $values) as $delta => $item) {
        unset($this->list[$delta]);
      }

      // Set the values.
      foreach ($values as $delta => $value) {
        if (!isset($this->list[$delta]) && is_numeric($delta)) {
          $this->list[$delta] = $this->createItem($value);
        }
        elseif (is_numeric($delta)) {
          $this->list[$delta]->setValue($value);
        }
        // @todo: Throw an exception else? Invalid value given?
      }
    }
    else {
      $this->list = array();
    }
  }

  /**
   * Returns a string representation of the property.
   *
   * @return string
   */
  public function getString() {
    $strings = array();
    foreach ($this->list() as $item) {
      $strings[] = $item->getString();
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
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    return array_key_exists($offset, $this->list);
  }

  /**
   * Implements ArrayAccess::offsetUnset().
   */
  public function offsetUnset($offset) {
    unset($this->list[$offset]);
  }

  /**
   * Implements ArrayAccess::offsetGet().
   */
  public function offsetGet($offset) {
    if (!isset($offset)) {
      // @todo: Needs tests.
      // The [] operator has been used so point at a new entry.
      $offset = $this->list ? max(array_keys($this->list)) + 1 : 0;
    }

    // Allow getting not yet existing items as well.
    // @todo: Maybe add a createItem() method instead or in addition?
    // @todo: Needs tests.
    if (!isset($this->list[$offset]) && is_numeric($offset)) {
      $this->list[$offset] = $this->createItem();
    }
    // @todo: Throw exception for not numeric offsets.

    return $this->list[$offset];
  }

  /**
   * Helper for creating a list item object.
   *
   * @return \Drupal\Core\Property\PropertyInterface
   */
  protected function createItem($value = NULL) {
    return drupal_get_property(array('list' => FALSE) + $this->definition, $value);
  }

  /**
   * Implements ArrayAccess::offsetSet().
   */
  public function offsetSet($offset, $value) {
    // @todo: Throw exception if the value does not implement the interface.
    if (is_numeric($offset)) {
      $this->list[$offset] = $value;
    }
    // @todo: Throw exception if offset is invalid.
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new \ArrayIterator($this->list);
  }

  /**
   * Implements Countable::count().
   */
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
    return $this->getValue();
  }

  public function access($account = NULL) {
    // TODO: Implement access() method. Use item access.
  }
}