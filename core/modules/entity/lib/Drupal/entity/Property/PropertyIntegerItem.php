<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyIntegerItem.
 */

namespace Drupal\entity\Property;
use \Drupal\entity\Property\EntityPropertyItemBase;


/**
 * Defines the 'integer_item' entity property item.
 */
class PropertyIntegerItem extends EntityPropertyItemBase {

  /**
   * Implements DataContainerInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Statically cache the definitions to avoid creating lots of array copies.
    $definitions = drupal_static(__CLASS__);

    if (!isset($definitions)) {
      $definitions['value'] = array(
        'type' => 'integer',
        'label' => t('Integer value'),
      );
    }
    return $definitions;
  }
}

