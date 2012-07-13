<?php
/**
 * @file
 * Definition of Drupal\entity\PropertyTypeEntityReferenceItem.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyTypeContainerInterface;
use Drupal\Core\Property\PropertyContainerInterface;


/**
 * Defines the 'entityreference_item' property type for entity properties.
 *
 * Entity property are always multiple valued, so the value is an numerically
 * indexed array of raw property item values. The raw property item value is
 * itself an array of raw values for the contained properties.
 *
 * @see entity_data_type_info()
 */
class PropertyTypeEntityReferenceItem implements PropertyTypeContainerInterface {

  /**
   * Implements \Drupal\Core\Property\PropertyTypeContainerInterface.
   */
  function getPropertyDefinitions(array $definition) {
    return array(
      'id' => array(
        // @todo: Lookup the definition of the entity type's id to get the right
        // property type for the id.
        'type' => 'integer',
        'label' => t('Entity id'),
      ),
      'entity' => array(
        'type' => 'entity',
        'entity type' => $definition['entity type'],
        'label' => t('Entity'),
        'description' => t('The referenced entity'),
      ),
    );
  }

  /**
   * Implements \Drupal\Core\Property\PropertyTypeContainerInterface.
   */
  function getProperty(array $definition, $value = NULL) {
    $value = isset($value) ? $value : array();
    return new EntityProperty($definition, $value, 'Drupal\entity\EntityPropertyEntityReferenceItem');
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
