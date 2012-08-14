<?php
/**
 * @file
 * Definition of Drupal\text\PropertyTextItem.
 */

namespace Drupal\text;
use \Drupal\entity\EntityPropertyItemBase;

/**
 * Defines the 'text_item' and 'text_long_item' entity property items.
 */
class PropertyTextItem extends EntityPropertyItemBase {

  /**
   * Implements PropertyContainerInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Statically cache the definitions to avoid creating lots of array copies.
    $definitions = drupal_static(__CLASS__);

    if (!isset($definitions)) {
      $definitions['value'] = array(
        'type' => 'string',
        'label' => t('Text value'),
      );
      $definitions['format'] = array(
        'type' => 'string',
        'label' => t('Text format'),
      );
      $definitions['processed'] = array(
        'type' => 'string',
        'label' => t('Processed text'),
        'description' => t('The text value with the text format applied.'),
        'html' => TRUE,
        'computed' => TRUE,
        'class' => '\Drupal\text\PropertyProcessedText',
        'source' => 'value',
      );
    }
    return $definitions;
  }
}

