<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Field\Type\DateItem.
 */

namespace Drupal\Core\Entity\Field\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'date_field' entity field item.
 *
 * Available settings (below the definition's 'settings' key) are:
 *   - property {NAME}: An array containing definition overrides for the
 *     property with the name {NAME}. For example, this can be used by a
 *     computed field to easily override the 'class' key of single field value
 *     only.
 */
class DateItem extends FieldItemBase {

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
        'type' => 'date',
        'label' => t('Date value'),
      );
    }
    return self::$propertyDefinitions;
  }
}
