<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyEntityReferenceItem.
 */

namespace Drupal\entity\Property;
use Drupal\entity\Property\EntityPropertyItemBase;
use InvalidArgumentException;


/**
 * Defines the 'entityreference_item' entity property item.
 */
class PropertyEntityReferenceItem extends EntityPropertyItemBase {

  /**
   * Property definitions of the contained properties.
   *
   * @see self::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements DataStructureInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Definitions vary by entity type, so key them by entity type.
    $entity_type = $this->definition['entity type'];

    if (!isset(self::$propertyDefinitions[$entity_type])) {
      self::$propertyDefinitions[$entity_type]['value'] = array(
        // @todo: Lookup the entity type's ID data type and use it here.
        'type' => 'integer',
        'label' => t('Entity ID'),
      );
      self::$propertyDefinitions[$entity_type]['entity'] = array(
        'type' => 'entity',
        'entity type' => $entity_type,
        'label' => t('Entity'),
        'description' => t('The referenced entity'),
        // The entity object is computed out of the entity id.
        'computed' => TRUE,
        'read-only' => FALSE,
      );
    }
    return self::$propertyDefinitions[$entity_type];
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
    if (!empty($values['value'])) {
      $this->properties['value']->setValue($values['value']);
    }
    else {
      $this->properties['entity']->setValue(isset($values['entity']) ? $values['entity'] : NULL);
    }
    unset($values['entity'], $values['value']);
    if ($values) {
      throw new InvalidArgumentException('Property ' . key($values) . ' is unknown.');
    }
  }
}
