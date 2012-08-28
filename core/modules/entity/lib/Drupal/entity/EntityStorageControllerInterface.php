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
   *
   * @param array $definition
   *   The definition of the entity of which to get property definitions, i.e.
   *   an array having an 'entity type' and optionally a 'bundle' key. For
   *   example:
   *   @code
   *   $definition = array(
   *     'type' => 'entity',
   *     'entity type' => 'node',
   *     'bundle' => 'article',
   *   );
   *   @endcode
   *
   * @return array
   *   An array of property definitions of entity properties, keyed by property
   *   name.
   */
  public function getPropertyDefinitions(array $definition);
}
