<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyEntityReferenceItem.
 */

namespace Drupal\entity\Property;
use \Drupal\entity\Property\EntityPropertyItemBase;


/**
 * Defines the 'entityreference_item' entity property item.
 */
class PropertyEntityReferenceItem extends EntityPropertyItemBase {

  /**
   * Implements DataContainerInterface::getPropertyDefinitions().
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
      'read-only' => FALSE,
    );
    return $definitions;
  }

  /**
   * Overrides EntityPropertyItemBase::setValue().
   */
  public function setValue($values) {
    // Treat the values as property value of the entity property, if no array
    // is given.
    if (!is_array($values)) {
      $values = array('entity' => $values);
    }

    // Entity is computed out of the ID, so we only need to update the ID. Only
    // set the entity property if no ID is given.
    if (!empty($values['id'])) {
      $this->properties['id']->setValue($values['id']);
    }
    else {
      $this->properties['entity']->setValue(isset($values['entity']) ? $values['entity'] : NULL);
    }
    unset($values['entity'], $values['id']);
    if ($values) {
      throw new \InvalidArgumentException('Property ' . key($values) . ' is unknown.');
    }
  }
}
