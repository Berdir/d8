<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyEntity.
 */

namespace Drupal\entity\Property;
use \Drupal\Core\Data\DataItemInterface;
use \Drupal\Core\Data\DataStructureInterface;


/**
 * Defines the 'entity' property type, e.g. the computed 'entity' property of entity references.
 *
 * This property implements the container interface, whereby most container
 * methods are just forwarded to the contained entity (if set).
 */
class PropertyEntity implements DataItemInterface, DataStructureInterface {

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
   * The property holding the entity ID.
   *
   * @var \Drupal\Core\Data\DataItemInterface
   */
  protected $id;

  /**
   * Implements DataItemInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;
    $this->entityType = isset($this->definition['entity type']) ? $this->definition['entity type'] : NULL;

    if (isset($context['parent'])) {
      $this->id = $context['parent']->get('id');
    }
    else {
      // No context given, so just initialize an ID property for storing the
      // entity ID.
      $this->id = drupal_get_property(array('type' => 'string'));
    }

    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements DataItemInterface::getType().
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements DataItemInterface::getDefinition().
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
   * Implements DataItemInterface::getValue().
   */
  public function getValue() {
    $id = $this->id->getValue();
    return $id ? entity_load($this->entityType, $id) : NULL;
  }

  /**
   * Implements DataItemInterface::setValue().
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
      throw new \InvalidArgumentException('Value is no valid entity.');
    }
  }

  /**
   * Implements DataItemInterface::getString().
   */
  public function getString() {
    $entity = $this->getValue();
    return $entity ? $entity->label() : '';
  }

  /**
   * Implements DataItemInterface::validate().
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
   * Implements DataStructureInterface::getProperties().
   */
  public function getProperties() {
    $entity = $this->getValue();
    return $entity ? $entity->getProperties() : array();
  }

  /**
   * Implements DataStructureInterface::setProperties().
   */
  public function setProperties($properties) {
    if ($entity = $this->getValue()) {
      $entity->setProperties($properties);
    }
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
    // @todo: Support getting definitions if multiple bundles are specified.
    $definitions = entity_get_controller($this->definition['entity type'])->getPropertyDefinitions($this->definition);

    return $definitions;
  }

  /**
   * Implements DataStructureInterface::toArray().
   */
  public function toArray() {
    $entity = $this->getValue();
    return $entity ? $entity->toArray() : array();
  }
}
