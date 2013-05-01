<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Field\Type\EntityReferenceItem.
 */

namespace Drupal\Core\Entity\Field\Type;

use Drupal\Core\Entity\Field\FieldItemBase;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Defines the 'entity_reference_field' entity field item.
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 * - target_bundle: (optional): If set, restricts the entity bundles which may
 *   may be referenced. May be set to an single bundle, or to an array of
 *   allowed bundles.
 */
class EntityReferenceItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see EntityReferenceItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Definitions vary by settings, so key them accordingly.
    $key = implode(',', $this->definition['settings']);

    if (!isset(self::$propertyDefinitions[$key])) {
      static::$propertyDefinitions[$key]['target_id'] = array(
        // @todo: Lookup the entity type's ID data type and use it here.
        'type' => 'integer',
        'label' => t('Entity ID'),
        'constraints' => array(
          'Range' => array('min' => 0),
        ),
      );
      static::$propertyDefinitions[$key]['entity'] = array(
        'type' => 'entity_reference',
        'constraints' => array(
          'EntityType' => $this->definition['settings']['target_type'],
        ),
        'label' => t('Entity'),
        'description' => t('The referenced entity'),
        // The entity object is computed out of the entity ID.
        'computed' => TRUE,
        'read-only' => FALSE,
        'settings' => array('source' => 'target_id'),
      );
      if (isset($this->definition['settings']['target_bundle'])) {
        static::$propertyDefinitions[$key]['entity']['constraints']['Bundle'] = $this->definition['settings']['target_bundle'];
      }
    }
    return static::$propertyDefinitions[$key];
  }

  /**
   * Overrides \Drupal\Core\Entity\Field\FieldItemBase::__get().
   */
  public function __get($name) {
    $name = ($name == 'value') ? 'target_id' : $name;
    return parent::__get($name);
  }

  /**
   * Overrides \Drupal\Core\Entity\Field\FieldItemBase::get().
   */
  public function get($property_name) {
    $property_name = ($property_name == 'value') ? 'target_id' : $property_name;
    return parent::get($property_name);
  }

  /**
   * Implements \Drupal\Core\Entity\Field\FieldItemInterface::__isset().
   */
  public function __isset($property_name) {
    $property_name = ($property_name == 'value') ? 'target_id' : $property_name;
    return parent::__isset($property_name);
  }

  /**
   * Overrides \Drupal\Core\Entity\Field\FieldItemBase::get().
   */
  public function setValue($values, $notify = TRUE) {
    // Treat the values as property value of the entity property, if no array is
    // given.
    if (isset($values) && !is_array($values)) {
      $values = array('entity' => $values);
    }
    // Make sure that the 'entity' property gets set as 'target_id'.
    if (isset($values['target_id']) && !isset($values['entity'])) {
      $values['entity'] = $values['target_id'];
    }
    parent::setValue($values, $notify);
  }
}
