<?php
/**
 * @file
 * Definition of Drupal\entity\PropertyTypeEntity.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyTypeContainerInterface;
use Drupal\Core\Property\PropertyContainerInterface;


/**
 * Defines the entity property type, i.e. properties of type 'entity'.
 *
 * The raw value of an entity reference property is a numerically indexed array
 * containing the entity type and the entity ID for entity-generic references,
 * or just the entity ID for entity type specific references.
 *
 * Property definitions of type 'entity' may contain keys further defining the
 * reference. Additionally supported keys are:
 *   - entity type: The entity type which is being referenced.
 *   - bundle: The bundle which is being referenced, or an array of possible
 *     bundles.
 *
 * @see entity_data_type_info()
 */
class PropertyTypeEntity implements PropertyTypeContainerInterface {

  /**
   * Gets an array property definitions of contained properties.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   */
  function getPropertyDefinitions(array $definition) {
    $definitions = array();
    if (!empty($definition['entity type'])) {
      $definitions = entity_get_controller($definition['entity type'])->basePropertyDefinitions();
      // Allow modules to add their own property definitions. E.g. this is
      // implemented by field.module to add definitions for its fields.
      drupal_alter('entity_property_definition', $definitions, $definition);
    }
    return $definitions;
  }

  /**
   * Gets the property object given the raw value.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   * @param $value
   *   The raw value of the property, or NULL if the property is not set. For
   *   entity references the raw value is the entity ID, whereas for other
   *   property containers it is usually an array of (raw) values matching the
   *   definitions of the contained properties.
   *
   * @return PropertyContainerInterface
   */
  function getProperty(array $definition, $value = NULL) {
    if (isset($value) && is_array($value)) {
      list($entity_type, $id) = $value;
      return entity_load($entity_type, $id);
    }
    elseif (isset($value)) {
      return entity_load($definition['entity type'], $value);
    }
  }

  /**
   * Gets the raw value of a property container object.
   *
   * @param array $definition
   *   The definition of the container's property, e.g. the definition of an
   *   entity reference property.
   * @param PropertyContainerInterface $value
   *
   * @return mixed
   */
  function getRawValue(array $definition, PropertyContainerInterface $entity) {
    return empty($definition['entity type']) ? array($entity->entityType(), $entity->id()) : $entity->id();
  }

  public function validate($value, array $definition) {
    // TODO: Implement validate() method.
  }
}
