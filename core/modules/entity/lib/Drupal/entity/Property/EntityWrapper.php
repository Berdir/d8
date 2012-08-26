<?php
/**
 * @file
 * Definition of Drupal\entity\Property\EntityWrapper.
 */

namespace Drupal\entity\Property;
use Drupal\Core\TypedData\DataWrapperInterface;
use Drupal\Core\TypedData\DataStructureInterface;
use InvalidArgumentException;

/**
 * Defines the 'entity' data type, e.g. the computed 'entity' property of entity references.
 *
 * This wrapper implements the DataStructureInterface, whereby most of its
 * methods are just forwarded to the wrapped entity (if set).
 */
class EntityWrapper implements DataWrapperInterface, DataStructureInterface {

  /**
   * The data definition.
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
   * The data wrapper holding the entity ID.
   *
   * @var \Drupal\Core\TypedData\DataWrapperInterface
   */
  protected $id;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;
    $this->entityType = isset($this->definition['entity type']) ? $this->definition['entity type'] : NULL;

    if (isset($context['parent'])) {
      $this->id = $context['parent']->get('value');
    }
    else {
      // No context given, so just initialize an ID property for storing the
      // entity ID.
      $this->id = drupal_wrap_data(array('type' => 'string'));
    }

    if (isset($value)) {
      $this->setValue($value);
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
   * Implements DataWrapperInterface::getValue().
   */
  public function getValue() {
    $id = $this->id->getValue();
    return $id ? entity_load($this->entityType, $id) : NULL;
  }

  /**
   * Implements DataWrapperInterface::setValue().
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
   * Implements DataWrapperInterface::getString().
   */
  public function getString() {
    $entity = $this->getValue();
    return $entity ? $entity->label() : '';
  }

  /**
   * Implements DataWrapperInterface::validate().
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
  public function getProperties($include_computed = FALSE) {
    $entity = $this->getValue();
    return $entity ? $entity->getProperties($include_computed) : array();
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
