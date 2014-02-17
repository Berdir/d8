<?php

/**
 * @file
 * Definition of Drupal\Core\Config\Entity\ConfigEntityInterface.
 */

namespace Drupal\Core\Config\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the interface common for all configuration entities.
 */
interface ConfigEntityInterface extends EntityInterface {

  /**
   * Returns the original ID.
   *
   * @return string|null
   *   The original ID or NULL.
   */
  public function getOriginalId();

  /**
   * Sets the original ID.
   *
   * @param string $id
   *   The new ID to set as original ID.
   *
   * @return $this
   */
  public function setOriginalId($id);

  /**
   * Enables the configuration entity.
   *
   * @return $this
   */
  public function enable();

  /**
   * Disables the configuration entity.
   *
   * @return $this
   */
  public function disable();

  /**
   * Sets the status of the configuration entity.
   *
   * @param bool $status
   *   The status of the configuration entity.
   *
   * @return $this
   */
  public function setStatus($status);

  /**
   * Sets the status of the isSyncing flag.
   *
   * @param bool $status
   *   The status of the sync flag.
   */
  public function setSyncing($status);

  /**
   * Returns whether the configuration entity is enabled.
   *
   * Status implementations for configuration entities should follow these
   * general rules:
   *   - Status does not affect the loading of entities. I.e. Disabling
   *     configuration entities should only have UI/access implications.
   *   - It should only take effect when a 'status' key is explicitly declared
   *     in the entity_keys info of a configuration entitys annotation data.
   *   - Each entity implementation (entity/controller) is responsible for
   *     checking and managing the status.
   *
   * @return bool
   *   Whether the entity is enabled or not.
   */
  public function status();

  /**
   * Returns if this entity is changed as part of an import process.
   *
   * Code that changes configuration based on new, changed or deleted
   * configuration entities must check this flag and only be executed if it is
   * FALSE.
   *
   * An example is the default body field that is created when a new content
   * type is created. If that creation happens as part of a configuration sync,
   * the default body field will either be explicitly created or has been
   * removed.
   *
   * @return bool
   *   TRUE if the configuration entity is created, updated or deleted through
   *   the import process.
   */
  public function isSyncing();

  /**
   * Returns the value of a property.
   *
   * @param string $property_name
   *   The name of the property that should be returned.
   *
   * @return mixed
   *   The property if it exists, or NULL otherwise.
   */
  public function get($property_name);

  /**
   * Sets the value of a property.
   *
   * @param string $property_name
   *   The name of the property that should be set.
   * @param mixed $value
   *   The value the property should be set to.
   */
  public function set($property_name, $value);

  /**
   * Retrieves the exportable properties of the entity.
   *
   * These are the values that get saved into config.
   *
   * @return mixed[]
   *   An array of exportable properties and their values.
   */
  public function getExportProperties();

}
