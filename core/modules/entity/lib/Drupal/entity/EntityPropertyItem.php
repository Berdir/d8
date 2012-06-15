<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyItem.
 */

namespace Drupal\entity;

/**
 * A list of PropertyContainer items.
 */
class EntityPropertyItem implements EntityPropertyItemInterface {

  protected $values = array();

  public function __construct($values = array()) {
    $this->values = $values;
  }

  public function __get($name) {
    return $this->get($name);
  }

  public function __set($name, $value) {
    $this->set($name, $value);
  }

  public function access($account) {

  }

  public function get($name) {
    return $this->values[$name];
  }

  public function getIterator() {

  }

  public function getProperties() {

  }

  public function getPropertyDefinitions() {

  }

  public function getRawValue($property_name) {

  }

  public function set($name, $value) {
    $this->values[$name] = $value;
  }

  public function validate() {

  }

}