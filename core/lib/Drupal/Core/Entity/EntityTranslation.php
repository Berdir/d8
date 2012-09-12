<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\EntityTranslation.
 */

namespace Drupal\Core\Entity;
use Drupal\Core\TypedData\Type\WrapperBase;
use Drupal\Core\TypedData\WrapperInterface;
use Drupal\Core\TypedData\StructureInterface;
use Drupal\Core\TypedData\AccessibleInterface;
use ArrayIterator;
use InvalidArgumentException;

/**
 * Makes translated entity properties available via the Property API.
 *
 * @todo: Needs an entity specific interface.
 */
class EntityTranslation extends WrapperBase implements StructureInterface, AccessibleInterface {

  /**
   * The array of translated properties, each being an instance of
   * ItemListInterface.
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
   * Implements WrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;
    $this->properties = (array) $value;
    $this->langcode = $context['langcode'];
  }

  /**
   * Implements WrapperInterface::getValue().
   */
  public function getValue() {
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements WrapperInterface::setValue().
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
   * Implements WrapperInterface::getString().
   */
  public function getString() {
    $strings = array();
    foreach ($this->getProperties() as $property) {
      $strings[] = $property->getString();
    }
    return implode(', ', array_filter($strings));
  }

  /**
   * Implements WrapperInterface::get().
   */
  public function get($property_name) {
    $definitions = $this->getPropertyDefinitions();
    if (!isset($definitions[$property_name])) {
      throw new InvalidArgumentException('Property ' . check_plain($property_name) . ' is unknown or not translatable.');
    }
    return $this->properties[$property_name];
  }

  /**
   * Implements StructureInterface::set().
   */
  public function set($property_name, $value) {
    $this->get($property_name)->setValue($value);
  }

  /**
   * Implements StructureInterface::getProperties().
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
   * Implements StructureInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($this->getProperties() as $name => $property) {
      if (isset($properties[$name])) {
        // Copy the value to our property object.
        $value = $properties[$name] instanceof WrapperInterface ? $properties[$name]->getValue() : $properties[$name];
        $property->setValue($value);
        unset($properties[$name]);
      }
    }
    if ($properties) {
      throw new InvalidArgumentException('Property ' . check_plain(key($values)) . ' is unknown or not translatable.');
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
    $this->get($name)->setValue($value);
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new ArrayIterator($this->getProperties());
  }

  /**
   * Implements StructureInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements StructureInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    $definitions = array();
    $entity_properties = entity_get_controller($this->definition['constraints']['entity type'])->getPropertyDefinitions(array(
      'entity type' => $this->definition['constraints']['entity type'],
      'bundle' => $this->definition['constraints']['bundle'],
    ));
    foreach ($entity_properties as $name => $definition) {
      if (!empty($definition['translatable'])) {
        $definitions[$name] = $definition;
      }
    }
    return $definitions;
  }

  /**
   * Implements StructureInterface::toArray().
   */
  public function toArray() {
    return $this->getValue();
  }

  /**
   * Implements StructureInterface::isEmpty().
   */
  public function isEmpty() {
    foreach ($this->getProperties() as $property) {
      if ($property->getValue() !== NULL) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Implements AccessibleInterface::access().
   */
  public function access(\Drupal\user\User $account = NULL) {
    // @todo implement
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate($value = NULL) {
    // @todo implement
  }
}
