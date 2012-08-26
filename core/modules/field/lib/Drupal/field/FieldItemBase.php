<?php

/*
 * @file
 * Definition of Drupal\field\FieldItemBase.
 */

namespace Drupal\field;
use Drupal\Core\TypedData\DataMissesContextException;
use Drupal\entity\Property\EntityPropertyItemBase;
use InvalidArgumentException;

/**
 * A field item.
 *
 * Field items making use of this base class have to implement
 * DataStructureInterface::getPropertyDefinitions().
 *
 * @see \Drupal\entity\Property\EntityPropertyItemBase
 */
abstract class FieldItemBase extends EntityPropertyItemBase implements FieldItemInterface {

  /**
   * The field context.
   *
   * @var array
   */
  protected $fieldContext;

  /**
   * Overrides EntityPropertyItemBase::__construct().
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
      throw new DataMissesContextException('Unable to get the field instance without field context.');
    }
    return field_info_instance($this->fieldContext['entity type'], $this->fieldContext['name'], $this->fieldContext['bundle']);
  }
}
