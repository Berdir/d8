<?php

/**
 * @file
 * Contains \Drupal\options\Type\ListTextItem.
 */

namespace Drupal\options\Type;

use Drupal\field\Plugin\field\field_type\LegacyCFieldItem;

/**
 * Defines the 'list_text' configurable field type.
 */
class ListTextItem extends LegacyCFieldItem {

  /**
   * Definitions of the contained properties.
   *
   * @see TextItem::getPropertyDefinitions()
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
        'type' => 'string',
        'label' => t('Text value'),
      );
    }
    return static::$propertyDefinitions;
  }

}
