<?php

/**
 * @file
 * Contains \Drupal\number\Type\FloatItem.
 */

namespace Drupal\number\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'number_float_field' entity field item.
 */
class FloatItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see self::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {

    if (!isset(self::$propertyDefinitions)) {
      self::$propertyDefinitions['value'] = array(
        'type' => 'float',
        'label' => t('Float value'),
      );
    }
    return self::$propertyDefinitions;
  }
}
