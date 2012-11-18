<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\ContextAwareInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for context aware data.
 */
interface ContextAwareInterface {

  /**
   * Returns the name of a property or item.
   *
   * @return string
   *   If the data is a property of some complex data, the name of the property.
   *   If the data is an item of a list, the name is the numeric position of the
   *   item in the list, starting with 0. Otherwise, NULL is returned.
   */
  public function getName();

  /**
   * Returns the typed data namespace of the typed data tree.
   *
   * A namespace to identify the current typed data tree, e.g. for the the tree
   * of typed data objects of an entity it could be
   * Drupal.core.entity.entity_type.
   *
   * @return string
   *   The namespace of the typed data tree, or NULL if it is not specified.
   */
  public function getNamespace();

  /**
   * Returns the property path of the data.
   *
   * The trail of property names relative to the root of the typed data tree,
   * separated by dots; e.g. 'field_text.0.format'.
   *
   * @return string
   *   If the data is a property of some complex data, the name of the property.
   *   If the data is an item of a list, the name is the numeric position of the
   *   item in the list, starting with 0. Otherwise, NULL is returned.
   */
  public function getPropertyPath();

  /**
   * Returns the parent data structure; i.e. either complex data or a list.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface|\Drupal\Core\TypedData\ListInterface
   *   The parent data structure; either complex data or a list.
   */
  public function getParent();

  /**
   * Sets the parent of a property or item.
   *
   * This method is supposed to be called by the factory only.
   *
   * @param mixed $parent
   *   The parent data structure; either complex data or a list.
   *
   * @see ContextAwareInterface::getParent()
   */
  public function setParent($parent);
}
