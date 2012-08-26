<?php
/**
 * @file
 * Definition of Drupal\text\PropertyTextWithSummaryItem.
 */

namespace Drupal\text;
use Drupal\text\PropertyTextItem;

/**
 * Defines the 'text_with_summary' entity property item.
 */
class PropertyTextWithSummaryItem extends PropertyTextItem {

  /**
   * Implements DataStructureInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Statically cache the definitions to avoid creating lots of array copies.
    $definitions = drupal_static(__CLASS__);

    if (!isset($definitions)) {

      $definitions = parent::getPropertyDefinitions();

      $definitions['summary'] = array(
        'type' => 'string',
        'label' => t('Summary text value'),
      );
      $definitions['summary_processed'] = array(
        'type' => 'string',
        'label' => t('Processed summary text'),
        'description' => t('The summary text value with the text format applied.'),
        'html' => TRUE,
        'computed' => TRUE,
        'class' => '\Drupal\text\PropertyProcessedText',
        'source' => 'summary',
      );
    }
    return $definitions;
  }
}

