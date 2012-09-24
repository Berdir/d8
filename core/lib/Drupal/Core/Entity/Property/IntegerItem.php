<?php
/**
 * @file
 * Definition of Drupal\Core\Entity\Property\IntegerItem.
 */

namespace Drupal\Core\Entity\Property;
use Drupal\Core\Entity\Property\ItemBase;


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
   * Implements ComplexDataInterface::getPropertyDefinitions().
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

