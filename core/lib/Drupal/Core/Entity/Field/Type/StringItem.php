<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Field\Type\StringItem.
 */

namespace Drupal\Core\Entity\Field\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'string_field' entity field item.
 *
 * Available settings (below the definition's 'settings' key) are:
 *   - property {NAME}: An array containing definition overrides for the
 *     property with the name {NAME}. For example, this can be used by a
 *     computed field to easily override the 'class' key of single field value
 *     only.
 */
class StringItem extends FieldItemBase {

  /**
   * Field definitions of the contained properties.
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
        'type' => 'string',
        'label' => t('Text value'),
      );
    }
    return self::$propertyDefinitions;
  }
}
