<?php

/**
 * @file
 * Definition of Drupal\entity\EntityTranslation.
 */

namespace Drupal\entity;

use Drupal\Core\TypedData\DataWrapperInterface;
use Drupal\Core\TypedData\DataStructureInterface;
use Drupal\Core\TypedData\DataAccessibleInterface;
use ArrayIterator;
use InvalidArgumentException;

/**
 * Makes translated entity properties available via the Property API.
 *
 * @todo: Needs an entity specific interface.
 */
class EntityTranslation implements DataStructureInterface, DataWrapperInterface, DataAccessibleInterface {

  /**
   * The property definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The array of translated properties, each being an instance of
   * EntityPropertyListInterface.
   *
   * @var array
   */
  protected $properties = array();

  /**
   * The language code of the translation.
   *
   * @var string
   */
  protected $langcode;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;
    $this->properties = (array) $value;
    $this->langcode = $context['langcode'];
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
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($values) {
    foreach ($this->getProperties() as $name => $property) {
      $property->setValue(isset($values[$name]) ? $values[$name] : NULL);
      unset($values[$name]);
    }
    if ($values) {
      throw new InvalidArgumentException('Property ' . check_plain(key($values)) . ' is unknown or not translatable.');
    }
  }

  /**
   * Implements DataWrapperInterface::getString().
   */
  public function getString() {
    $strings = array();
    foreach ($this->getProperties() as $property) {
      $strings[] = $property->getString();
    }
    return implode(', ', array_filter($strings));
  }

  /**
   * Implements DataWrapperInterface::get().
   */
  public function get($property_name) {
    $definitions = $this->getPropertyDefinitions();
    if (!isset($definitions[$property_name])) {
      throw new InvalidArgumentException('Property ' . check_plain(key($values)) . ' is unknown or not translatable.');
    }
    return $this->properties[$property_name];
  }

  /**
   * Implements DataStructureInterface::getProperties().
   */
  public function getProperties($include_computed = FALSE) {
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if ($include_computed || empty($definition['computed'])) {
        $properties[$name] = $this->get($name);
      }
    }
    return $properties;
  }

  /**
   * Implements DataStructureInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($properties as $name => $property) {
      // Copy the value to our property object.
      $value = $property instanceof DataWrapperInterface ? $property->getValue() : $property;
      $this->get($name)->setValue($value);
    }
  }

  /**
   * Magic getter: Gets the translated property.
   */
  public function __get($name) {
    return $this->get($name);
  }

  /**
   * Magic getter: Sets the translated property.
   */
  public function __set($name, $value) {
    $value = $value instanceof DataWrapperInterface ? $value->getValue() : $value;
    $this->get($name)->setValue($value);
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new ArrayIterator($this->getProperties());
  }

  /**
   * Implements DataStructureInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements DataStructureInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    $definitions = array();
    $entity_properties = entity_get_controller($this->definition['entity type'])->getPropertyDefinitions(array(
      'type' => 'entity',
      'entity type' => $this->definition['entity type'],
      'bundle' => $this->definition['bundle'],
    ));
    foreach ($entity_properties as $name => $definition) {
      if (!empty($definition['translatable'])) {
        $definitions[$name] = $definition;
      }
    }
    return $definitions;
  }

  /**
   * Implements DataStructureInterface::toArray().
   */
  public function toArray() {
    return $this->getValue();
  }

  /**
   * Implements DataAccessibleInterface::access().
   */
  public function access(\Drupal\user\User $account = NULL) {
    // @todo implement
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate($value = NULL) {
    // @todo implement
  }
}
