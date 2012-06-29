<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyEntityReferenceItem.
 */

namespace Drupal\entity;
use \Drupal\Core\Property\PropertyTypeContainerInterface;
use \Drupal\Core\Property\PropertyContainerInterface;

/**
 * An entity property item for entityreference items.
 *
 * @see EntityPropertyItemInterface
 */
class EntityPropertyEntityReferenceItem extends EntityPropertyItem {

  public function getRawValue($property_name) {
    if ($property_name == 'entity') {
      // Just get the id for the entity.
      $property_name = 'id';
    }
    return parent::getRawValue($property_name);
  }

  public function set($property_name, $value) {
    if ($property_name == 'entity') {
      // Get the id of the entity first.
      if ($value instanceof PropertyContainerInterface) {
        $definition = $this->getPropertyDefinition($property_name);
        $data_type = drupal_get_property_type_plugin($definition['type']);
        $value = $data_type->getRawValue($definition, $value);
      }
      $property_name = 'id';
    }
    parent::set($property_name, $value);
    $this->properties = array();
  }
}


