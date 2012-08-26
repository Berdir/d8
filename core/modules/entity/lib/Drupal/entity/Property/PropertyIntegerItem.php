<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyIntegerItem.
 */

namespace Drupal\entity\Property;
use Drupal\entity\Property\EntityPropertyItemBase;


/**
 * Defines the 'integer_item' entity property item.
 */
class PropertyIntegerItem extends EntityPropertyItemBase {

  /**
   * Property definitions of the contained properties.
   *
   * @see self::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements DataStructureInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {

    if (!isset(self::$propertyDefinitions)) {
      self::$propertyDefinitions['value'] = array(
        'type' => 'integer',
        'label' => t('Integer value'),
      );
    }
    return self::$propertyDefinitions;
  }
}

