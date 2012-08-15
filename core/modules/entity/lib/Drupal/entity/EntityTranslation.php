<?php

/**
 * @file
 * Definition of Drupal\entity\EntityTranslation.
 */

namespace Drupal\entity;

use Drupal\Core\Property\PropertyInterface;
use Drupal\Core\Property\PropertyContainerInterface;

/**
 * Makes translated entity properties available via the Property API.
 */
class EntityTranslation implements PropertyContainerInterface, PropertyInterface {

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
   * Implements PropertyInterface::__construct().
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
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements PropertyInterface::setValue().
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
   * Implements PropertyInterface::getString().
   */
  public function getString() {
    $strings = array();
    foreach ($this->getProperties() as $property) {
      $strings[] = $property->getString();
    }
    return implode(', ', array_filter($strings));
  }

  /**
   * Implements PropertyInterface::get().
   */
  public function get($property_name) {
    $definitions = $this->getPropertyDefinitions();
    if (!isset($definitions[$property_name])) {
      throw new \InvalidArgumentException('Property ' . check_plain(key($values)) . ' is unknown or not translatable.');
    }
    return $this->entity->get($property_name, $this->definition['langcode']);
  }

  /**
   * Implements PropertyContainerInterface::getProperties().
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
   * Implements PropertyContainerInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($properties as $name => $property) {
      // Copy the value to our property object.
      $value = $property instanceof PropertyInterface ? $property->getValue() : $property;
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
    $value = $value instanceof PropertyInterface ? $value->getValue() : $value;
    $this->get($name)->setValue($value);
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new \ArrayIterator($this->getProperties());
  }

  /**
   * Implements PropertyContainerInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements PropertyContainerInterface::getPropertyDefinitions().
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
   * Implements PropertyContainerInterface::toArray().
   */
  public function toArray() {
    return $this->getValue();
  }

  public function access($account = NULL) {
    // @todo implement
  }

  /**
   * Implements PropertyInterface::validate().
   */
  public function validate($value = NULL) {
    // @todo implement
  }
}
