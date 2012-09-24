<?php
/**
 * @file
 * Definition of Drupal\text\FieldTextItem.
 */

namespace Drupal\text;
use Drupal\field\FieldItemBase;

/**
 * Defines the 'text_item' and 'text_long_item' entity property items.
 */
class FieldTextItem extends FieldItemBase {

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
        'class' => '\Drupal\text\FieldTextProcessed',
        'settings' => array(
          'text source' => 'value',
        ),
      );
    }
    return self::$propertyDefinitions;
  }
}

