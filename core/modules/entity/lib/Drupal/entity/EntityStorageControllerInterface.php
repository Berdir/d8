<?php

/**
 * @file
 * Definition of Drupal\entity\EntityStorageControllerInterface.
 */

namespace Drupal\entity;

/**
 * Defines a common interface for entity controller classes.
 *
 * All entity controller classes specified via the 'controller class' key
 * returned by hook_entity_info() or hook_entity_info_alter() have to implement
 * this interface.
 *
 * Most simple, SQL-based entity controllers will do better by extending
 * Drupal\entity\DatabaseStorageController instead of implementing this
 * interface directly.
 */
interface EntityStorageControllerInterface extends StorageControllerInterface {

  /**
   * Gets an array of entity property definitions.
   *
   * If a 'bundle' key is present in the given entity definition, properties
   * specific to this bundle are included.
   * Entity properties are always multi-valued, so 'list' is TRUE for each
   * returned property definition.
   *
   * @param array $constraints
   *   An array of entity constraints as used for entities in typed data
   *   definitions, i.e. an array having an 'entity type' and optionally a
   *   'bundle' key. For example:
   *   @code
   *   array(
   *     'entity type' => 'node',
   *     'bundle' => 'article',
   *   )
   *   @endcode
   *
   * @return array
   *   An array of property definitions of entity properties, keyed by property
   *   name. In addition to the typed data definition keys as described at
   *   drupal_wrap_data() the follow keys are supported:
   *   - queryable: Whether the property is queryable via EntityFieldQuery.
   *     Defaults to TRUE if 'computed' is FALSE or not set, to FALSE otherwise.
   *   - translatable: Whether the property is translatable. Defaults to FALSE.
   *   - field: A boolean indicating whether the property is a field. Defaults
   *     to FALSE.
   *
   * @see drupal_wrap_data()
   */
  public function getPropertyDefinitions(array $constraints);
}
