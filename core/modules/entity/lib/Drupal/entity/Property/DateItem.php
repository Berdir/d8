<?php
/**
 * @file
 * Definition of Drupal\entity\Property\DateItem.
 */

namespace Drupal\entity\Property;
use Drupal\entity\Property\ItemBase;


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
   * Implements StructureInterface::getPropertyDefinitions().
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

