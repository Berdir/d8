<?php

/**
 * @file
 * Definition of Drupal\entity\EntityProperty.
 */

namespace Drupal\entity;

/**
 * A list of PropertyContainer items.
 */
class EntityProperty implements EntityPropertyInterface {

  // Numerically indexed array of PropertyContainer objects
  protected $list;

  public function __construct($list = array()) {
    $this->list = $list;
  }


  public function access($account) {
    // TODO: Implement access() method. Use item access.
  }

  public function validate() {
    // TODO: Implement validate() method.
  }

  public function offsetExists($offset) {
    return array_key_exists($offset, $this->list);
  }

  public function offsetSet($offset, $value) {
    $this->list[$offset] = $value;
  }

  public function offsetUnset($offset) {
    unset($this->list[$offset]);
  }

  public function offsetGet($offset) {
    return $this->list[$offset];
  }

  public function getIterator() {
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
}