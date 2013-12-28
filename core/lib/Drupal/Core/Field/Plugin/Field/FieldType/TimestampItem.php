<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\Field\FieldType\TimestampItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'timestamp' entity field type.
 *
 * @FieldType(
 *   id = "timestamp",
 *   label = @Translation("Timestamp"),
 *   description = @Translation("An entity field containing a UNIX timestamp value."),
 *   configurable = FALSE
 * )
 */
class TimestampItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = DataDefinition::create('timestamp')
        ->setLabel(t('Timestamp value'));
    }
    return static::$propertyDefinitions;
  }

}
