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
   * The array of properties, each being either an object or a primitive.
   *
   * @var array
   */
  protected $properties = array();

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

    foreach ($values as $name => $value) {
      $this->set($name, $value);
    }
  }

  public function getRawValue($property_name) {
    return isset($this->values[$property_name]) ? $this->values[$property_name] : NULL;
  }

  public function get($property_name) {
    // Populate $this->properties to fasten further lookups and to keep track of
    // property objects, possibly holding changes to properties.

    if (!array_key_exists($property_name, $this->properties)) {
      $definition = $this->getPropertyDefinition($property_name);
      $data_type = drupal_get_property_type_plugin($definition['type']);
      $value = $this->getRawValue($property_name);

      if ($data_type instanceof PropertyTypeContainerInterface) {
        $this->properties[$property_name] = $data_type->getProperty($definition, $value);
      }
      else {
        $this->properties[$property_name] = $value;
      }
    }
    return $this->properties[$property_name];
  }

  public function set($property_name, $value) {
    // If an object is passed in, get the raw value.
    // @todo: Needs tests.
    if ($value instanceof PropertyContainerInterface) {
      $definition = $this->getPropertyDefinition($property_name);
      $data_type = drupal_get_property_type_plugin($definition['type']);
      $value = $data_type->getRawValue($definition, $value);
    }

    $this->values[$property_name] = $value;
    unset($this->properties[$property_name]);
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