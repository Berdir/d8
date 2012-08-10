<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyTextItem.
 */

namespace Drupal\entity\Property;
use \Drupal\entity\EntityPropertyItemBase;


/**
 * Defines the 'text_item' entity property item.
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
    }
    return $definitions;
  }
}

