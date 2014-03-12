<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\DataType\EntityTypedDataWrapper.
 */

namespace Drupal\Core\Entity\Plugin\DataType;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Defines the base plugin for deriving data types for entity types.
 *
 * Note that the class only registers the plugin, and is actually never used.
 * \Drupal\Core\Entity\Entity is available for use as base class.
 *
 * @DataType(
 *   id = "entity",
 *   label = @Translation("Entity"),
 *   description = @Translation("All kind of entities, e.g. nodes, comments or users."),
 *   derivative = "\Drupal\Core\Entity\Plugin\DataType\Deriver\EntityDeriver",
 *   definition_class = "\Drupal\Core\Entity\TypedData\EntityDataDefinition"
 * )
 */
class EntityTypedDataWrapper implements \IteratorAggregate, ComplexDataInterface {

  /**
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  public function __construct(ContentEntityInterface $entity) {
    $this->entity = $entity;
  }

  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function get($property_name) {
    $this->entity->get($property_name);
  }

  /**
   * {@inheritdoc}
   */
  public function set($property_name, $value, $notify = TRUE) {
    return $this->entity->set($property_name, $value, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties($include_computed = FALSE) {
    return $this->entity->getFields($include_computed);
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyValues() {
    return $this->entity->getFieldValues();
  }

  /**
   * {@inheritdoc}
   */
  public function setPropertyValues($values) {
    // TODO: Implement setPropertyValues() method.
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // TODO: Implement isEmpty() method.
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name) {
    $this->entity->onChange($property_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getDataDefinition() {
    return $this->entity->getDataDefinition();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    // TODO: Implement getValue() method.
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    // TODO: Implement setValue() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getString() {
    // TODO: Implement getString() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    return $this->entity->validate();
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    // TODO: Implement applyDefaultValue() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    // TODO: Implement getName() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    // TODO: Implement getParent() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getRoot() {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyPath() {
    // TODO: Implement getPropertyPath() method.
  }

  /**
   * {@inheritdoc}
   */
  public function setContext($name = NULL, TypedDataInterface $parent = NULL) {
    // TODO: Implement setContext() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return $this->entity->getIterator();
  }

}
