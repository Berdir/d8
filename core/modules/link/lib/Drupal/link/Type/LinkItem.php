<?php

/**
 * @file
 * Contains \Drupal\link\Type\LinkItem.
 */

namespace Drupal\link\Type;

use Drupal\field\Plugin\field\field_type\LegacyCFieldItem;

/**
 * Defines the 'link_field' entity field item.
 */
class LinkItem extends LegacyCFieldItem {

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
      self::$propertyDefinitions['url'] = array(
        'type' => 'uri',
        'label' => t('URL'),
      );
      self::$propertyDefinitions['title'] = array(
        'type' => 'string',
        'label' => t('Link text'),
      );
      self::$propertyDefinitions['attributes'] = array(
        'type' => 'map',
        'label' => t('Attributes'),
      );
    }
    return self::$propertyDefinitions;
  }
}
