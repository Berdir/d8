<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\Type\FieldType\CField.
 */

namespace Drupal\field\Plugin\Type\FieldType;

use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Field\Type\Field;
use Drupal\field\Field as FieldAPI;
use Drupal\Core\Language\Language;

/**
 * Represents a configurable entity field.
 */
class CField extends Field {

  /**
   * The Field instance definition.
   *
   * @var \Drupal\field\Plugin\Core\Entity\FieldInstance
   */
  protected $instance;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $definition, $plugin_id, array $plugin_definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $plugin_id, $plugin_definition, $name, $parent);
    if (isset($definition['instance'])) {
      $this->instance = $definition['instance'];
    }
    else {
      $instances = FieldAPI::fieldInfo()->getBundleInstances($parent->entityType(), $parent->bundle());
      $this->instance = $instances[$name];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = array();
    // Check that the number of values doesn't exceed the field cardinality. For
    // form submitted values, this can only happen with 'multiple value'
    // widgets.
    $cardinality = $this->instance->getField()->cardinality;
    if ($cardinality != FIELD_CARDINALITY_UNLIMITED) {
      $constraints[] = \Drupal::typedData()
        ->getValidationConstraintManager()
        ->create('Count', array(
          'max' => $cardinality,
          'maxMessage' => t('%name: this field cannot hold more than @count values.', array('%name' => $this->instance->label, '@count' => $cardinality)),
        ));
    }

    return $constraints;
  }

  // @todo... former code in field.default.inc

  /**
   * {@inheritdoc}
   */
  public function prepareTranslation(EntityInterface $source_entity, $source_langcode) {
    $field = $this->field;

    // @todo Adapt...

    // If the field is untranslatable keep using LANGCODE_NOT_SPECIFIED.
    if ($langcode == Language::LANGCODE_NOT_SPECIFIED) {
      $source_langcode = Language::LANGCODE_NOT_SPECIFIED;
    }
    if (isset($source_entity->{$field->id}[$source_langcode])) {
      $items = $source_entity->{$field->id}[$source_langcode];
    }
  }

}
