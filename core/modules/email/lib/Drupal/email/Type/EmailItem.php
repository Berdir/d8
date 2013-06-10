<?php

/**
 * @file
 * Contains \Drupal\email\Type\EmailItem.
 */

namespace Drupal\email\Type;

use Drupal\field\Plugin\field\field_type\LegacyCFieldItem;

/**
 * Defines the 'email_field' entity field item.
 */
class EmailItem extends LegacyCFieldItem {

  /**
   * Definitions of the contained properties.
   *
   * @see EmailItem::getPropertyDefinitions()
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
        'type' => 'email',
        'label' => t('E-mail value'),
      );
    }
    return static::$propertyDefinitions;
  }
}
