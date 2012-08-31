<?php
/**
 * @file
 * Definition of Drupal\text\FieldTextWithSummaryItem.
 */

namespace Drupal\text;

/**
 * Defines the 'text_with_summary' entity property item.
 */
class FieldTextWithSummaryItem extends FieldTextItem {

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

      self::$propertyDefinitions = parent::getPropertyDefinitions();

      self::$propertyDefinitions['summary'] = array(
        'type' => 'string',
        'label' => t('Summary text value'),
      );
      self::$propertyDefinitions['summary_processed'] = array(
        'type' => 'string',
        'label' => t('Processed summary text'),
        'description' => t('The summary text value with the text format applied.'),
        'html' => TRUE,
        'computed' => TRUE,
        'class' => '\Drupal\text\FieldTextProcessed',
        'source' => 'summary',
      );
    }
    return self::$propertyDefinitions;
  }
}

