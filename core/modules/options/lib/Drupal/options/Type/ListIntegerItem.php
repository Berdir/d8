<?php

/**
 * @file
 * Contains \Drupal\options\Type\ListIntegerItem.
 */

namespace Drupal\options\Type;

use Drupal\field\Plugin\field\field_type\LegacyCFieldItem;

/**
 * Defines the 'list_integer' entity field item.
 */
class ListIntegerItem extends LegacyCFieldItem {

  /**
   * Definitions of the contained properties.
   *
   * @see IntegerItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = array(
        'type' => 'integer',
        'label' => t('Integer value'),
      );
    }
    return static::$propertyDefinitions;
  }
}
