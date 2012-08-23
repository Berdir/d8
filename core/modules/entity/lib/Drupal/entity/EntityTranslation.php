<?php

/**
 * @file
 * Definition of Drupal\entity\EntityTranslation.
 */

namespace Drupal\entity;

use Drupal\Core\TypedData\DataWrapperInterface;
use Drupal\Core\TypedData\DataContainerInterface;

/**
 * Makes translated entity properties available via the Property API.
 */
class EntityTranslation implements DataContainerInterface, DataWrapperInterface {

  /**
   * The property definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The entity of which we make property translations available.
   *
   * @var EntityNG
   */
  protected $entity;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;

    if (empty($context['parent'])) {
      throw new \InvalidArgumentException('Missing context, i.e. the entity to work with.');
    }
    $this->entity = $context['parent'];

    if (empty($this->definition['langcode'])) {
      throw new \InvalidArgumentException('Missing language code');
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
      throw new \InvalidArgumentException('Property ' . check_plain(key($values)) . ' is unknown or not translatable.');
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
      throw new \InvalidArgumentException('Property ' . check_plain(key($values)) . ' is unknown or not translatable.');
    }
    return $this->entity->get($property_name, $this->definition['langcode']);
  }

  /**
   * Implements DataContainerInterface::getProperties().
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
   * Implements DataContainerInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($properties as $name => $property) {
      // Copy the value to our property object.
      $value = $property instanceof DataWrapperInterface ? $property->getValue() : $property;
      $this->get($name)->setValue($value);
    }
  }

  /**
   * Magic getter: Gets the property in default language.
   */
  public function __get($name) {
    return $this->get($name);
  }

  /**
   * Magic getter: Sets the property in default language.
   */
  public function __set($name, $value) {
    $value = $value instanceof DataWrapperInterface ? $value->getValue() : $value;
    $this->get($name)->setValue($value);
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new \ArrayIterator($this->getProperties());
  }

  /**
   * Implements DataContainerInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements DataContainerInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    $definitions = array();
    foreach ($this->entity->getPropertyDefinitions() as $name => $definition) {
      if (!empty($definition['translatable'])) {
        $definitions[$name] = $definition;
      }
    }
    return $definitions;
  }

  /**
   * Implements DataContainerInterface::toArray().
   */
  public function toArray() {
    return $this->getValue();
  }

  public function access($account = NULL) {
    // @todo implement
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate($value = NULL) {
    // @todo implement
  }
}
