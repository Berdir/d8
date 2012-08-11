<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyEntity.
 */

namespace Drupal\entity\Property;
use \Drupal\Core\Property\PropertyInterface;
use \Drupal\Core\Property\PropertyContainerInterface;


/**
 * Defines the 'entity' property type, e.g. the computed 'entity' property of entity references.
 *
 * This property implements the container interface, whereby most container
 * methods are just forwarded to the contained entity (if set).
 */
class PropertyEntity implements PropertyInterface, PropertyContainerInterface {

  /**
   * The property definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The referenced entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The entity ID.
   *
   * @var mixed
   */
  protected $id;

  /**
   * Implements PropertyInterface::__construct().
   */
  function __construct(array $definition, $value = NULL) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
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
   *
   * Property definitions of type 'entity' may contain keys further defining the
   * reference. Additionally supported keys are:
   *   - entity type: The entity type which is being referenced.
   *   - bundle: The bundle which is being referenced, or an array of possible
   *     bundles.
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements PropertyInterface::getValue().
   */
  public function getValue() {
    return $this->id ? entity_load($this->entityType, $this->id) : NULL;
  }

  /**
   * Implements PropertyInterface::setValue().
   *
   * Both the entity ID and the entity object may be passed as value.
   */
  public function setValue($value) {
    if (!isset($value)) {
      $this->id = NULL;
    }
    elseif (is_scalar($value) && !empty($this->definition['entity type'])) {
      $this->id = $value;
      $this->entityType = $this->definition['entity type'];
    }
    elseif ($value instanceof \Drupal\entity\EntityInterface) {
      $this->id = $value->id();
      $this->entityType = $value->entityType();
    }
    // @todo: Through exception if invalid value is passed.
  }

  /**
   * Implements PropertyInterface::getString().
   */
  public function getString() {
    $entity = $this->getValue();
    return $entity ? $entity->label() : '';
  }

  /**
   * Implements PropertyInterface::validate().
   */
  public function validate($value = NULL) {
    // TODO: Implement validate() method.
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    $entity = $this->getValue();
    return $entity ? $entity->getIterator() : new \ArrayIterator(array());
  }

  /**
   * Implements PropertyContainerInterface::getProperties().
   */
  public function getProperties() {
    $entity = $this->getValue();
    return $entity ? $entity->getProperties() : array();
  }

  /**
   * Implements PropertyContainerInterface::setProperties().
   */
  public function setProperties($properties) {
    if ($entity = $this->getValue()) {
      $entity->setProperties($properties);
    }
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
    // @todo: Support getting definitions if multiple bundles are specified.
    $bundle = isset($this->definition['bundle']) ? $this->definition['bundle'] : NULL;
    $definitions = entity_get_controller($this->definition['entity type'])->getPropertyDefinitions($bundle);

    return $definitions;
  }

  /**
   * Implements PropertyContainerInterface::toArray().
   */
  public function toArray() {
    $entity = $this->getValue();
    return $entity ? $entity->toArray() : array();
  }
}
