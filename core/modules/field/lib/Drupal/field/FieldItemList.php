<?php

/**
 * @file
 * Definition of Drupal\field\FieldItemListInterface.
 */

namespace Drupal\field;

use Drupal\Core\Entity\Property\ItemList;
use Drupal\Core\TypedData\MissingContextException;
use InvalidArgumentException;

/**
 * A list of field items.
 */
class FieldItemList extends ItemList implements FieldItemListInterface {

  /**
   * The field context.
   *
   * @var array
   */
  protected $fieldContext;

  /**
   * Implements TypedDataInterface::setContext().
   */
  public function setContext(array $context) {
    if (isset($context['parent'])) {
      $this->setFieldContext($context['parent']->entityType(), $context['name'], $context['parent']->bundle());
    }
  }

  /**
   * Implements FieldItemListInterface::setFieldContext().
   */
  public function setFieldContext($entity_type, $name, $bundle) {
    $this->fieldContext['entity type'] = $entity_type;
    $this->fieldContext['name'] = $name;
    $this->fieldContext['bundle'] = $bundle;
  }

  /**
   * Implements FieldItemListInterface::getFieldContext().
   */
  public function getFieldContext() {
    return $this->fieldContext;
  }

  /**
   * Implements FieldItemList::getInstance().
   */
  public function getInstance() {
    if (!isset($this->fieldContext)) {
      throw new MissingContextException('Unable to get the field instance without field context.');
    }
    return field_info_instance($this->fieldContext['entity type'], $this->fieldContext['name'], $this->fieldContext['bundle']);
  }
}
