<?php

/**
 * @file
 * Definition of Drupal\text\Type\TextSummaryItem.
 */

namespace Drupal\text\Type;

/**
 * Defines the 'text_with_summary_field' entity field item.
 *
 * Available settings (below the definition's 'settings' key) are:
 *   - property {NAME}: An array containing definition overrides for the
 *     property with the name {NAME}. For example, this can be used by a
 *     computed field to easily override the 'class' key of single field value
 *     only.
 */
class TextSummaryItem extends TextItem {

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

      self::$propertyDefinitions = parent::getPropertyDefinitions();

      self::$propertyDefinitions['summary'] = array(
        'type' => 'string',
        'label' => t('Summary text value'),
      );
      self::$propertyDefinitions['summary_processed'] = array(
        'type' => 'string',
        'label' => t('Processed summary text'),
        'description' => t('The summary text value with the text format applied.'),
        'computed' => TRUE,
        'class' => '\Drupal\text\TextProcessed',
        'settings' => array(
          'text source' => 'summary',
        ),
      );
    }
    return self::$propertyDefinitions;
  }
}
