<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\EntityTranslation.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\TypedData\Type\TypedData;
use Drupal\Core\TypedData\AccessibleInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;

/**
 * Makes translated entity properties available via the Property API.
 *
 * @todo: Needs an entity specific interface.
 */
class EntityTranslation extends TypedData implements IteratorAggregate, ComplexDataInterface, AccessibleInterface {

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
   * Implements TypedDataInterface::setContext().
   */
  public function setContext(array $context) {
    if (isset($context['langcode']) && isset($context['properties'])) {
      $this->properties = $context['properties'];
      $this->langcode = $context['langcode'];
    }
  }

  /**
   * Implements TypedDataInterface::getValue().
   */
  public function getValue() {
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements TypedDataInterface::setValue().
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
   * Implements TypedDataInterface::getString().
   */
  public function getString() {
    $strings = array();
    foreach ($this->getProperties() as $property) {
      $strings[] = $property->getString();
    }
    return implode(', ', array_filter($strings));
  }

  /**
   * Implements TypedDataInterface::get().
   */
  public function get($property_name) {
    $definitions = $this->getPropertyDefinitions();
    if (!isset($definitions[$property_name])) {
      throw new InvalidArgumentException('Property ' . check_plain($property_name) . ' is unknown or not translatable.');
    }
    return $this->properties[$property_name];
  }

  /**
   * Implements ComplexDataInterface::set().
   */
  public function set($property_name, $value) {
    $this->get($property_name)->setValue($value);
  }

  /**
   * Implements ComplexDataInterface::getProperties().
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
   * Implements ComplexDataInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
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
   * Implements ComplexDataInterface::getPropertyValues().
   */
  public function getPropertyValues() {
    return $this->getValue();
  }

  /**
   * Implements ComplexDataInterface::setPropertyValues().
   */
  public function setPropertyValues($values) {
    foreach ($values as $name => $value) {
      $this->get($name)->setValue($value);
    }
  }

  /**
   * Implements ComplexDataInterface::isEmpty().
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
   * Implements TypedDataInterface::validate().
   */
  public function validate($value = NULL) {
    // @todo implement
  }
}
