<?php
/**
 * @file
 * Definition of Drupal\entity\PropertyTypeTextItem.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyTypeContainerInterface;
use Drupal\Core\Property\PropertyContainerInterface;


/**
 * Defines the 'text_item' property type for entity properties.
 *
 * Entity property are always multiple valued, so the value is an numerically
 * indexed array of raw property item values. The raw property item value is
 * itself an array of raw values for the contained properties.
 *
 * @see entity_data_type_info()
 */
class PropertyTypeTextItem implements PropertyTypeContainerInterface {

  /**
   * Implements \Drupal\Core\Property\PropertyTypeContainerInterface.
   */
  function getPropertyDefinitions(array $definition) {
    return array(
      'value' => array(
        'type' => 'string',
        'label' => t('Text value'),
      ),
    );
  }

  /**
   * Implements \Drupal\Core\Property\PropertyTypeContainerInterface.
   */
  function getProperty(array $definition, $value = NULL) {
    $value = isset($value) ? $value : array();
    return new EntityProperty($definition, $value);
  }

  /**
   * Implements \Drupal\Core\Property\PropertyTypeContainerInterface.
   */
  function getRawValue(array $definition, $item) {
    return $item->toArray();
  }

  public function validate($value, array $definition) {
    // TODO: Implement validate() method.
  }
}
