<?php

/*
 * @file
 * Definition of Drupal\field\FieldItemListInterface.
 */

namespace Drupal\field;
use Drupal\Core\TypedData\MissingContextException;
use Drupal\Core\Entity\Property\ItemList;
use InvalidArgumentException;

/**
 * Interface for entity properties that are fields, being lists of field items.
 */
class FieldItemList extends ItemList implements FieldItemListInterface {

  /**
   * The field context.
   *
   * @var array
   */
  protected $fieldContext;

  /**
   * Overrides ItemList::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    parent::__construct($definition, $value, $context);
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
