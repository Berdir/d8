<?php
/**
 * @file
 * Definition of Drupal\Core\Entity\Property\EntityReferenceItem.
 */

namespace Drupal\Core\Entity\Property;
use Drupal\Core\Entity\Property\ItemBase;
use InvalidArgumentException;


/**
 * Defines the 'entityreference_item' entity property item.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - entity type: The entity type to reference.
 */
class EntityReferenceItem extends ItemBase {

  /**
   * Property definitions of the contained properties.
   *
   * @see self::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Definitions vary by entity type, so key them by entity type.
    $entity_type = $this->definition['settings']['entity type'];

    if (!isset(self::$propertyDefinitions[$entity_type])) {
      self::$propertyDefinitions[$entity_type]['value'] = array(
        // @todo: Lookup the entity type's ID data type and use it here.
        'type' => 'integer',
        'label' => t('Entity ID'),
      );
      self::$propertyDefinitions[$entity_type]['entity'] = array(
        'type' => 'entity',
        'constraints' => array(
          'entity type' => $entity_type,
        ),
        'label' => t('Entity'),
        'description' => t('The referenced entity'),
        // The entity object is computed out of the entity id.
        'computed' => TRUE,
        'read-only' => FALSE,
        'settings' => array('id source' => 'value'),
      );
    }
    return self::$propertyDefinitions[$entity_type];
  }

  /**
   * Overrides ItemBase::setValue().
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
