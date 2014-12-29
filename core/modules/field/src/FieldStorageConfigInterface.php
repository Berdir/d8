<?php

/**
 * @file
 * Contains \Drupal\field\FieldStorageConfigInterface.
 */

namespace Drupal\field;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Provides an interface defining a field storage entity.
 */
interface FieldStorageConfigInterface extends ConfigEntityInterface, FieldStorageDefinitionInterface {

  /**
   * Returns the list of bundles where the field storage has fields.
   *
   * @return array
   *   An array of bundle names.
   */
  public function getBundles();

  /**
   * Returns whether the field storage is locked or not.
   *
   * @return bool
   *   TRUE if the field storage is locked.
   */
  public function isLocked();

  /**
   * Checks if the field storage can be deleted.
   *
   * @return bool
   *   TRUE if the field storage can be deleted.
   */
  public function isDeletable();

  /**
   * Sets the locked flag.
   *
   * @param bool $locked
   *   Sets value of locked flag.
   *
   * @return $this
   */
  public function setLocked($locked);

  /**
   * Returns whether the field is deleted or not.
   *
   * @return bool
   *   The deleted property.
   */
  public function isDeleted();

  /**
   * Sets the maximum number of items allowed for the field.
   *
   * @param int $cardinality
   *   The cardinality value.
   *
   * @return $this
   */
  public function setCardinality($cardinality);

  /**
   * Sets the value for a field setting by name.
   *
   * @param string $setting_name
   *   The name of the setting.
   * @param mixed $value
   *   The value of the setting.
   *
   * @return $this
   */
  public function setSetting($setting_name, $value);

  /**
   * Sets field settings by overwriting the settings array.
   *
   * @param string $settings
   *   The array of field settings.
   *
   * @return $this
   */
  public function setSettings($settings);

  /**
   * Returns the name of the module providing the field type.
   *
   * @return string
   *   The name of the module that provides the field type.
   */
  public function getModule();

  /**
   * Sets whether the field is translatable.
   *
   * @param bool $translatable
   *   Whether the field is translatable.
   *
   * @return $this
   */
  public function setTranslatable($translatable);

}
