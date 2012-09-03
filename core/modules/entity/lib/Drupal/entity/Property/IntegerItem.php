<?php
/**
 * @file
 * Definition of Drupal\entity\Property\IntegerItem.
 */

namespace Drupal\entity\Property;
use Drupal\entity\Property\ItemBase;


/**
 * Defines the 'integer_item' entity property item.
 */
class IntegerItem extends ItemBase {

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
        'type' => 'integer',
        'label' => t('Integer value'),
      );
    }
    return self::$propertyDefinitions;
  }
}

