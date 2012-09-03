<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyStringItem.
 */

namespace Drupal\entity\Property;
use Drupal\entity\Property\ItemBase;


/**
 * Defines the 'string_item' entity property item.
 */
class StringItem extends ItemBase {

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
        'type' => 'string',
        'label' => t('Text value'),
      );
    }
    return self::$propertyDefinitions;
  }
}

