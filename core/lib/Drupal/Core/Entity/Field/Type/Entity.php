<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Field\Type\AbstractEntity.
 */

namespace Drupal\Core\Entity\Field\Type;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityNG;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\ContextAwareInterface;
use Drupal\Core\TypedData\ContextAwareTypedData;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedData;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;

/**
 * Defines the (abstract) 'entity' data type.
 *
 * The entity data type is abstract; i.e., data cannot directly be an instance
 * of 'entity', but instead it can be an instance of some of its sub-types; for
 * example 'entity:user' or 'entity:node:article'. Entity types that make use of
 * bundles cannot be instantiated without bundles either, i.e. entity:node is
 * an abstract type as well as it requires a bundle for instantiation.
 *
 * As abstract types cannot be instantiated directly, it's not possible to set a
 * value on an abstract type object, thus the object is unset and read-only.
 * Still, abstract typed data objects allow dealing with metadata associated
 * with the abstract type. For example, the typed data object of 'entity:node'
 * allows you to iterate over the base fields defined for any node.
 *
 * @todo: Provide a way to update the definition after instantiating.
 *
 * Supported constraints (below the definition's 'constraints' key) are:
 *  - EntityType: The entity type.
 *  - Bundle: The bundle or an array of possible bundles.
 */
class Entity extends TypedData implements IteratorAggregate, ComplexDataInterface {

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::getValue().
   */
  public function getValue() {
    return NULL;
  }

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::setValue().
   *
   * Both the entity ID and the entity object may be passed as value.
   */
  public function setValue($value, $notify = TRUE) {
    if (isset($value)) {
      throw new InvalidArgumentException("Cannot set a value for an abstract type.");
    }
  }

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::getString().
   */
  public function getString() {
    return '';
  }

  /**
   * Implements \IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new \ArrayIterator($this->getProperties());
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::get().
   */
  public function get($property_name) {
    return typed_data()->getPropertyInstance($this, $property_name);
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::set().
   */
  public function set($property_name, $value, $notify = TRUE) {
    throw new InvalidArgumentException("Cannot set a property of an abstract type.");
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getProperties().
   */
  public function getProperties($include_computed = FALSE) {
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if (empty($definition['computed']) || $include_computed) {
        $properties[$name] = typed_data()->getPropertyInstance($this, $name);
      }
    }
    return $properties;
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    if (isset($definitions[$name])) {
      return $definitions[$name];
    }
    else {
      return FALSE;
    }
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // @todo: Support getting definitions if multiple bundles are specified.
    if (isset($this->definition['constraints']['EntityType'])) {
      return drupal_container()->get('plugin.manager.entity')
        ->getStorageController($this->definition['constraints']['EntityType'])
        ->getFieldDefinitions($this->definition['constraints']);
    }
    else {
      return array();
    }
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getPropertyValues().
   */
  public function getPropertyValues() {
    return array();
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::setPropertyValues().
   */
  public function setPropertyValues($values) {
    throw new InvalidArgumentException("Cannot set a property of an abstract type.");
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::isEmpty().
   */
  public function isEmpty() {
    return TRUE;
  }

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::onChange().
   */
  public function onChange($property_name) {
    // Nothing to do.
  }
}
