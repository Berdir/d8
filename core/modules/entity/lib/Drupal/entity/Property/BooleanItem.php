<?php
/**
 * @file
 * Definition of Drupal\entity\Property\BooleanItem.
 */

namespace Drupal\entity\Property;
use Drupal\entity\Property\ItemBase;


/**
 * Defines the 'boolean_item' entity property item.
 */
class BooleanItem extends ItemBase {

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
        'type' => 'boolean',
        'label' => t('Boolean value'),
      );
    }
    return self::$propertyDefinitions;
  }
}

