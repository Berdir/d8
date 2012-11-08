<?php

/**
 * @file
 * Definition of Drupal\text\Type\TextItem.
 */

namespace Drupal\text\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'text_field' and 'text_long_field' entity field items.
 *
 * Available settings (below the definition's 'settings' key) are:
 *   - property {NAME}: An array containing definition overrides for the
 *     property with the name {NAME}. For example, this can be used by a
 *     computed field to easily override the 'class' key of single field value
 *     only.
 */
class TextItem extends FieldItemBase {

  /**
   * Field definitions of the contained properties.
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
        'type' => 'string',
        'label' => t('Text value'),
      );
      self::$propertyDefinitions['format'] = array(
        'type' => 'string',
        'label' => t('Text format'),
      );
      self::$propertyDefinitions['processed'] = array(
        'type' => 'string',
        'label' => t('Processed text'),
        'description' => t('The text value with the text format applied.'),
        'computed' => TRUE,
        'class' => '\Drupal\text\TextProcessed',
        'settings' => array(
          'text source' => 'value',
        ),
      );
    }
    return self::$propertyDefinitions;
  }
}
