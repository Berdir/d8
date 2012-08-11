<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyEntityReferenceItem.
 */

namespace Drupal\entity\Property;
use \Drupal\entity\EntityPropertyItemBase;


/**
 * Defines the 'entityreference_item' entity property item.
 */
class PropertyEntityReferenceItem extends EntityPropertyItemBase {

  /**
   * Implements PropertyContainerInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // @todo: Avoid creating multiple array copies if used multiple times.

    $definitions['id'] = array(
      // @todo: Lookup the entity type's ID data type and use it here.
      'type' => 'integer',
      'label' => t('Entity ID'),
    );
    $definitions['entity'] = array(
      'type' => 'entity',
      'entity type' => $this->definition['entity type'],
      'label' => t('Entity'),
      'description' => t('The referenced entity'),
      // The entity object is computed out of the entity id.
      'computed' => TRUE,
    );
    return $definitions;
  }

  /**
   * Overrides EntityPropertyItemBase::get().
   */
  public function get($property_name) {
    // @todo: Somehow reset the property if the ID changes.
    if ($property_name == 'entity') {
      // Instantiate the property with the Entity ID.
      $id = isset($this->properties['id']) ? $this->properties['id']->getValue() : NULL;
      $this->properties[$property_name] = drupal_get_property($this->getPropertyDefinition($property_name), $id);
    }
    return parent::get($property_name);
  }
}
