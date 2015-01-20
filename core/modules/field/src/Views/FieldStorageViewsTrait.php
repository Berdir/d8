<?php

/**
 * @file
 * Contains \Drupal\field\Views\FieldStorageViewsTrait.
 */

namespace Drupal\field\Views;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * A trait containing helper methods for field definitions.
 */
trait FieldStorageViewsTrait {

  /**
   * The field definition to use.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface
   */
  protected $fieldDefinition;

  /**
   * The field storage definition.
   *
   * @var \Drupal\field\FieldStorageConfigInterface
   */
  protected $fieldStorageDefinition;

  /**
   * Gets the field definition.
   *
   * A field storage definition turned into a field definition, so it can be
   * used with widgets and formatters.
   *
   * @see BaseFieldDefinition::createFromFieldStorageDefinition().
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   *   The field definition used by this handler.
   */
  protected function getFieldDefinition() {
    if (!$this->fieldDefinition) {
      $field_storage_config = $this->getFieldStorageDefinition();
      $this->fieldDefinition = BaseFieldDefinition::createFromFieldStorageDefinition($field_storage_config);
    }
    return $this->fieldDefinition;
  }

  /**
   * Gets the field configuration.
   *
   * @return \Drupal\field\FieldStorageConfigInterface
   */
  protected function getFieldStorageDefinition() {
    if (!$this->fieldStorageDefinition) {
      $field_storage_definitions = \Drupal::entityManager()->getFieldStorageDefinitions($this->definition['entity_type']);
      $this->fieldStorageDefinition = $field_storage_definitions[$this->definition['field_name']];
    }
    return $this->fieldStorageDefinition;
  }

}
