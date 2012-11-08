<?php

/**
 * @file
 * Definition of Drupal\comment\FieldNewValue.
 */

namespace Drupal\comment;

use Drupal\Core\TypedData\ContextAwareInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\Type\Integer;
use Drupal\Core\TypedData\ReadOnlyException;
use InvalidArgumentException;

/**
 * A computed property for the integer value of the 'new' field.
 *
 * @todo: Declare the list of allowed values once supported.
 */
class FieldNewValue extends Integer implements ContextAwareInterface {

  /**
   * The name.
   *
   * @var string
   */
  protected $name;

  /**
   * The parent data structure.
   *
   * @var \Drupal\Core\Entity\Field\FieldItemInterface
   */
  protected $parent;

  /**
   * Implements ContextAwareInterface::getName().
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Implements ContextAwareInterface::setName().
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * Implements ContextAwareInterface::getParent().
   *
   * @return \Drupal\Core\Entity\Field\FieldItemInterface
   */
  public function getParent() {
    return $this->parent;
  }

  /**
   * Implements ContextAwareInterface::setParent().
   */
  public function setParent($parent) {
    $this->parent = $parent;
  }

  /**
   * Implements TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {

    if (!isset($this->parent)) {
      throw new InvalidArgumentException('Computed properties require context for computation.');
    }
    $field = $this->parent->getParent();
    $entity = $field->getParent();
    return node_mark($entity->nid->value, $entity->changed->value->getTimestamp());
  }

  /**
   * Implements TypedDataInterface::setValue().
   */
  public function setValue($value) {
    if (isset($value)) {
      throw new ReadOnlyException('Unable to set a computed property.');
    }
  }
}
