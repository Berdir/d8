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
 * @see EntityPropertyItemInterface
 */
class EntityPropertyItem implements EntityPropertyItemInterface {

  /**
   * The raw data values of the contained properties.
   *
   * @var array
   */
  protected $values = array();

  /**
   * The definition of the represented property.
   *
   * @var array
   */
  protected $definition;

  /**
   * The property's data type plugin.
   *
   * @var \Drupal\Core\Property\PropertyTypeContainerInterface
   */
  protected $dataType;


  public function __construct(array $definition, array $values = array()) {
    $this->definition = $definition;
    // @todo: Use dependency injection.
    $this->dataType = drupal_get_property_type_plugin($this->definition['type']);
    $this->values = $values;
  }

  public function getRawValue($property_name) {
    return isset($this->values[$property_name]) ? $this->values[$property_name] : NULL;
  }

  public function get($property_name) {
    // @todo: What about possible name clashes?
    if (!property_exists($this, $property_name)) {
      $definition = $this->getPropertyDefinition($property_name);
      $data_type = drupal_get_property_type_plugin($definition['type']);
      if ($data_type instanceof PropertyTypeContainerInterface) {
        return $data_type->getProperty($definition, $this->values[$property_name]);
      }
      else {
        return $this->values[$property_name];
      }
    }
  }

  public function set($property_name, $value) {
    $definition = $this->getPropertyDefinition($property_name);
    $data_type = drupal_get_property_type_plugin($definition['type']);

    if ($data_type instanceof PropertyTypeContainerInterface) {
      // Transform container objects back to raw values before setting if
      // necessary. Support passing in raw values as well.
      // @todo: Needs tests.
      if ($value instanceof PropertyContainerInterface) {
        $value = $data_type->getRawValue($definition, $value);
      }
      $this->values[$property_name] = $value;
      unset($this->$property_name);
    }
    else {
      // Just update the internal value. $this->$name is a reference on it, so
      // it will automatically reflect the update too.
      $this->values[$property_name] = $value;
    }
  }

  public function __get($name) {
    return $this->get($name);
  }

  public function __set($name, $value) {
    $this->set($name, $value);
  }

  public function getIterator() {
    // @todo implement
  }

  public function getProperties() {
    // @todo implement
  }

  public function getPropertyDefinition($name) {
    $definitions = $this->dataType->getPropertyDefinitions($this->definition);
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  public function getPropertyDefinitions() {
    return $this->dataType->getPropertyDefinitions($this->definition);
  }

  public function access($account) {
    // @todo implement
  }
  public function validate() {
    // @todo implement
  }
}