<?php

/**
 * @file
 * Definition of Drupal\entity\Property\EntityPropertyList.
 */

namespace Drupal\entity\Property;
use Drupal\user\User;

/**
 * An entity property list.
 *
 * An entity property is a list of property items, which contain only primitive
 * properties or entity references. Note that even single-valued entity
 * properties are represented as list of items, however for easy access to the
 * contained item the entity property delegates __get() and __set() calls
 * directly to the first item.
 *
 * @see EntityPropertyListInterface.
 */
class EntityPropertyList implements EntityPropertyListInterface {

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
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;
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
    foreach ($this->list as $delta => $item) {
      // @todo: Filter out empty items and add an isEmpty() method to them.
      $values[$delta] = $item->getValue();
    }
    return $values;
  }

  /**
   * Implements DataWrapperInterface::setValue().
   *
   * @param array $values
   *   An array of values of the property items.
   */
  public function setValue($values) {
    if (isset($values)) {

      // Support passing in property objects as value.
      if ($values instanceof DataWrapperInterface) {
        $values = $values->getValue();
      }
      if (!is_array($values)) {
        throw new \InvalidArgumentException("An entity property requires a numerically indexed array of items as value.");
      }

      // Clear the values of properties for which no value has been passed.
      foreach (array_diff_key($this->list, $values) as $delta => $item) {
        unset($this->list[$delta]);
      }

      // Set the values.
      foreach ($values as $delta => $value) {
        if (!is_numeric($delta)) {
          throw new \InvalidArgumentException('Unable to set a value with a non-numeric delta in a list.');
        }
        elseif (!isset($this->list[$delta])) {
          $this->list[$delta] = $this->createItem($value);
        }
        else {
          $this->list[$delta]->setValue($value);
        }
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
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
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

    if (!is_numeric($offset)) {
      throw new \InvalidArgumentException('Unable to get a value with a non-numeric delta in a list.');
    }
    // Allow getting not yet existing items as well.
    // @todo: Maybe add a public createItem() method instead or in addition?
    // @todo: Needs tests.
    elseif (!isset($this->list[$offset])) {
      $this->list[$offset] = $this->createItem();
    }

    return $this->list[$offset];
  }

  /**
   * Helper for creating a list item object.
   *
   * @return \Drupal\Core\TypedData\DataWrapperInterface
   */
  protected function createItem($value = NULL) {
    return drupal_wrap_data(array('list' => FALSE) + $this->definition, $value);
  }

  /**
   * Implements ArrayAccess::offsetSet().
   */
  public function offsetSet($offset, $value) {
    // @todo: Throw exception if the value does not implement the interface.
    if (is_numeric($offset)) {
      $this->offsetGet($offset)->setValue($value);
    }
    else {
      throw new \InvalidArgumentException('Unable to set a value with a non-numeric delta in a list.');
    }
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

  /**
   * Implements a deep clone.
   */
  public function __clone() {
    foreach ($this->list as $delta => $property) {
      $this->list[$delta] = clone $property;
    }
  }

  /**
   * Implements DataAccessibleInterface::access().
   */
  public function access(User $account = NULL) {
    // TODO: Implement access() method. Use item access.
  }
}
