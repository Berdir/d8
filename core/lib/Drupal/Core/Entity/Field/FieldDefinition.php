<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Field\FieldDefinition.
 */

namespace Drupal\Core\Entity\Field;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * A class for defining entity fields.
 */
class FieldDefinition extends DataDefinition implements FieldDefinitionInterface, \ArrayAccess {

  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return $this->definition['field_name'];
  }

  /**
   * Sets the field name.
   *
   * @param string $name
   *   The field name to set.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setFieldName($name) {
    $this->definition['field_name'] = $name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldType() {
    // Cut of the leading field_item: prefix from 'field_item:FIELD_TYPE'.
    $parts = explode(':', $this->getItemDefinition()->getType());
    return $parts[1];
  }

  /**
   * Sets the field type.
   *
   * @param string $type
   *   The field type to set.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setFieldType($type) {
    $this->definition['type'] = 'entity_field';
    $this->definition['item_definition']['type'] = 'field_item:' . $type;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldSettings() {
    return $this->definition['item_definition']['settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldSetting($setting_name) {
    return $this->definition['item_definition']['settings'][$setting_name];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldPropertyNames() {
    return array_keys(\Drupal::typedData()->create($this->getItemDefinition())->getPropertyDefinitions());
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldTranslatable() {
    return !empty($this->definition['translatable']);
  }

  /**
   * Sets whether the field is translatable.
   *
   * @param boolean $translatable
   *   Whether the field is translatable.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setTranslatable($translatable) {
    $this->definition['translatable'] = $translatable;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldLabel() {
    return $this->definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldDescription() {
    return $this->definition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldCardinality() {
    // @todo: Support reading out a possible cardinality constraint?
    return $this->isList() ? FIELD_CARDINALITY_UNLIMITED : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldRequired() {
    return !empty($this->definition['required']);
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldQueryable() {
    return isset($this->definition['queryable']) ? $this->definition['queryable'] : !$this->isComputed();
  }

  /**
   * Sets whether the field is queryable.
   *
   * @param boolean $queryable
   *   Whether the field is queryable.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setQueryable($queryable) {
    $this->definition['queryable'] = $queryable;
    return $this;
  }

  /**
   * Sets constraints for a given field item property.
   *
   * @param string $name
   *   The name of the property to set constraints for.
   * @param array $constraints
   *   The constraints to set.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setPropertyConstraints($name, array $constraints) {
    $this->definition['item_definition']['constraints']['ComplexData'][$name] = $constraints;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldConfigurable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldDefaultValue(EntityInterface $entity) {
    return $this->getFieldSetting('default_value');
  }

  /**
   * Allows creating field definition objects from old style definition arrays.
   *
   * @todo: Remove once no old-style definition arrays need to be supported.
   */
  public static function createFromOldStyleDefinition($field_name, array $definition) {
    unset($definition['list']);

    $list_definition = $definition;
    $list_definition['type'] = 'entity_field';
    unset($list_definition['constraints']);
    unset($list_definition['settings']);
    $new = new FieldDefinition($list_definition);

    if (isset($definition['list_class'])) {
      $new->setClass($definition['list_class']);
    }
    // Apply the rest to the item definition.
    $new->setItemDefinition(new FieldDefinition($definition));
    $new->setFieldName($field_name);
    return $new;
  }

}
