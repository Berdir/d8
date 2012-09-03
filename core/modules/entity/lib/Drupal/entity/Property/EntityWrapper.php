<?php
/**
 * @file
 * Definition of Drupal\entity\Property\EntityWrapper.
 */

namespace Drupal\entity\Property;
use Drupal\Core\TypedData\Type\WrapperBase;
use Drupal\Core\TypedData\WrapperInterface;
use Drupal\Core\TypedData\StructureInterface;
use ArrayIterator;
use InvalidArgumentException;

/**
 * Defines the 'entity' data type, e.g. the computed 'entity' property of entity references.
 *
 * This wrapper implements the StructureInterface, whereby most of its
 * methods are just forwarded to the wrapped entity (if set).
 */
class EntityWrapper extends WrapperBase implements StructureInterface {

  /**
   * The referenced entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The data wrapper holding the entity ID.
   *
   * @var \Drupal\Core\TypedData\WrapperInterface
   */
  protected $id;

  /**
   * Implements WrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;
    $this->entityType = isset($this->definition['entity type']) ? $this->definition['entity type'] : NULL;

    // If an ID source is specified, act as computed property.
    if (isset($context['parent']) && !empty($this->definition['settings']['id source'])) {
      $this->id = $context['parent']->get($this->definition['settings']['id source']);
    }
    else {
      // No context given, so just initialize an ID property for storing the
      // entity ID of the wrapped entity.
      $this->id = drupal_wrap_data(array('type' => 'string'));
    }

    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements WrapperInterface::getValue().
   */
  public function getValue() {
    $id = $this->id->getValue();
    return $id ? entity_load($this->entityType, $id) : NULL;
  }

  /**
   * Implements WrapperInterface::setValue().
   *
   * Both the entity ID and the entity object may be passed as value.
   */
  public function setValue($value) {
    if (!isset($value)) {
      $this->id->setValue(NULL);
    }
    elseif (is_scalar($value) && !empty($this->definition['entity type'])) {
      $this->id->setValue($value);
    }
    elseif ($value instanceof \Drupal\entity\EntityInterface) {
      $this->id->setValue($value->id());
      $this->entityType = $value->entityType();
    }
    else {
      throw new InvalidArgumentException('Value is no valid entity.');
    }
  }

  /**
   * Implements WrapperInterface::getString().
   */
  public function getString() {
    $entity = $this->getValue();
    return $entity ? $entity->label() : '';
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate($value = NULL) {
    // TODO: Implement validate() method.
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    $entity = $this->getValue();
    return $entity ? $entity->getIterator() : new ArrayIterator(array());
  }

  /**
   * Implements StructureInterface::get().
   */
  public function get($property_name) {
    $entity = $this->getValue();
    // @todo: Allow navigating through the tree without data as well.
    return $entity ? $entity->get($property_name) : NULL;
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
    $entity = $this->getValue();
    return $entity ? $entity->getProperties($include_computed) : array();
  }

  /**
   * Implements StructureInterface::setProperties().
   */
  public function setProperties($properties) {
    if ($entity = $this->getValue()) {
      $entity->setProperties($properties);
    }
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
    // @todo: Support getting definitions if multiple bundles are specified.
    return entity_get_controller($this->definition['entity type'])->getPropertyDefinitions($this->definition);
  }

  /**
   * Implements StructureInterface::toArray().
   */
  public function toArray() {
    $entity = $this->getValue();
    return $entity ? $entity->toArray() : array();
  }
}
