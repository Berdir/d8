<?php
/**
 * @file
 * Definition of Drupal\Core\Entity\Property\DateItem.
 */

namespace Drupal\Core\Entity\Property;
use Drupal\Core\Entity\Property\ItemBase;


/**
 * Defines the 'date_item' entity property item.
 */
class DateItem extends ItemBase {

  /**
   * Property definitions of the contained properties.
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

