<?php

/*
 * @file
 * Definition of Drupal\field\FieldItemBase.
 */

namespace Drupal\field;
use Drupal\Core\TypedData\MissingContextException;
use Drupal\entity\Property\ItemBase;
use InvalidArgumentException;

/**
 * A field item.
 *
 * Field items making use of this base class have to implement
 * StructureInterface::getPropertyDefinitions().
 *
 * @see \Drupal\entity\Property\ItemBase
 */
abstract class FieldItemBase extends ItemBase implements FieldItemInterface {

  /**
   * The field context.
   *
   * @var array
   */
  protected $fieldContext;

  /**
   * Overrides ItemBase::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    parent::__construct($definition, $value, $context);
    if (isset($context['parent'])) {
      $this->fieldContext = $context['parent']->getFieldContext();
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
