<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyStringItem.
 */

namespace Drupal\entity\Property;
use \Drupal\entity\Property\EntityPropertyItemBase;


/**
 * Defines the 'string_item' entity property item.
 */
class PropertyStringItem extends EntityPropertyItemBase {

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

