<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\Field\FieldType\IntegerItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'integer' entity field type.
 *
 * @FieldType(
 *   id = "integer",
 *   label = @Translation("Integer"),
 *   description = @Translation("An entity field containing an integer value."),
 *   configurable = FALSE
 * )
 */
class IntegerItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Integer value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
          'not null' => TRUE,
        ),
      ),
    );
  }

}
