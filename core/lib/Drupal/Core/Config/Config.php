<?php

/**
 * @file
 * Definition of Drupal\Core\Config\Config.
 */

namespace Drupal\Core\Config;

use Drupal\Component\Utility\NestedArray;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Defines the default configuration object.
 */
class Config {

  /**
   * The name of the configuration object.
   *
   * @var string
   */
  protected $name;

  /**
   * Whether the configuration object is new or has been saved to the storage.
   *
   * @var bool
   */
  protected $isNew = TRUE;

  /**
   * The data of the configuration object.
   *
   * @var array
   */
  protected $data;

  /**
   * The overridden data of the configuration object.
   *
   * @var array
   */
  protected $overrides = array();

  /**
   * The current runtime data ($data + $overrides).
   *
   * @var array
   */
  protected $overriddenData;

  /**
   * The storage used to load and save this configuration object.
   *
   * @var Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * The event dispatcher used to notify subscribers.
   *
   * @var Symfony\Component\EventDispatcher\EventDispatcher
   */
  protected $eventDispatcher;

  /**
   * Whether the config object has already been loaded.
   *
   * Aside from TRUE or FALSE it can be NULL when inside load() to signal the
   * "being loaded" phase.
   *
   * @var bool
   */
  protected $isLoaded = FALSE;

  /**
   * Constructs a configuration object.
   *
   * @param string $name
   *   The name of the configuration object being constructed.
   * @param Drupal\Core\Config\StorageInterface $storage
   *   A storage controller object to use for reading and writing the
   *   configuration data.
   * @param Symfony\Component\EventDispatcher\EventDispatcher $event_dispatcher
   *   The event dispatcher used to notify subscribers.
   */
  public function __construct($name, StorageInterface $storage, EventDispatcher $event_dispatcher = NULL) {
    $this->name = $name;
    $this->storage = $storage;
    $this->eventDispatcher = $event_dispatcher ? $event_dispatcher : drupal_container()->get('dispatcher');
  }

  /**
   * Initializes a configuration object.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function init() {
    $this->isLoaded = FALSE;
    $this->overrides = array();
    $this->notify('init');
    return $this;
  }

  /**
   * Returns the name of this configuration object.
   *
   * @return string
   *   The name of the configuration object.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Sets the name of this configuration object.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * Returns whether this configuration object is new.
   *
   * @return bool
   *   TRUE if this config object does not exist in storage.
   */
  public function isNew() {
    if (!$this->isLoaded) {
      $this->load();
    }
    return $this->isNew;
  }

  /**
   * Gets data from this config object.
   *
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *   For instance in the following configuation array:
   *   @code
   *   array(
   *     'foo' => array(
   *       'bar' => 'baz',
   *     ),
   *   );
   *   @endcode
   *   A key of 'foo.bar' would return the string 'baz'. However, a key of 'foo'
   *   would return array('bar' => 'baz').
   *   If no key is specified, then the entire data array is returned.
   *
   * The configuration system does not retain data types. Every saved value is
   * casted to a string. In most cases this is not an issue; however, it can
   * cause issues with Booleans, which are casted to "1" (TRUE) or "0" (FALSE).
   * In particular, code relying on === or !== will no longer function properly.
   *
   * @see http://php.net/manual/language.operators.comparison.php
   *
   * @return mixed
   *   The data that was requested.
   */
  public function get($key = '') {
    if (!$this->isLoaded) {
      $this->load();
    }
    if (!isset($this->overriddenData)) {
      $this->setOverriddenData();
    }
    if (empty($key)) {
      return $this->overriddenData;
    }
    else {
      $parts = explode('.', $key);
      if (count($parts) == 1) {
        return isset($this->overriddenData[$key]) ? $this->overriddenData[$key] : NULL;
      }
      else {
        $value = NestedArray::getValue($this->overriddenData, $parts, $key_exists);
        return $key_exists ? $value : NULL;
      }
    }
  }

  /**
   * Replaces the data of this configuration object.
   *
   * @param array $data
   *   The new configuration data.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function setData(array $data) {
    // A load would destroy the data just set (for example on import). Do not
    // set when inside load().
    if (isset($this->isLoaded)) {
      $this->isLoaded = TRUE;
    }
    $this->data = $data;
    $this->resetOverriddenData();
    return $this;
  }

  /**
   * Sets overridden data for this configuration object.
   *
   * The overridden data only applies to this configuration object.
   *
   * @param array $data
   *   The overridden values of the configuration data.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function setOverride(array $data) {
    $this->overrides = NestedArray::mergeDeepArray(array($this->overrides, $data), TRUE);
    $this->resetOverriddenData();
    return $this;
  }

  /**
   * Sets the current data for this configuration object.
   *
   * Merges overridden configuration data into the original data.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  protected function setOverriddenData() {
    $this->overriddenData = $this->data;
    if (!empty($this->overrides)) {
      $this->overriddenData = NestedArray::mergeDeepArray(array($this->overriddenData, $this->overrides), TRUE);
    }
    return $this;
  }

  /**
   * Resets the current data, so overrides are re-applied.
   *
   * This method should be called after the original data or the overridden data
   * has been changed.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  protected function resetOverriddenData() {
    unset($this->overriddenData);
    return $this;
  }

  /**
   * Sets value in this config object.
   *
   * @param string $key
   *   Identifier to store value in config.
   * @param string $value
   *   Value to associate with identifier.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function set($key, $value) {
    if (!$this->isLoaded) {
      $this->load();
    }
    // Type-cast value into a string.
    $value = $this->castValue($value);

    // The dot/period is a reserved character; it may appear between keys, but
    // not within keys.
    $parts = explode('.', $key);
    if (count($parts) == 1) {
      $this->data[$key] = $value;
    }
    else {
      NestedArray::setValue($this->data, $parts, $value);
    }
    $this->resetOverriddenData();
    return $this;
  }

  /**
   * Casts a saved value to a string.
   *
   * The configuration system only saves strings or arrays. Any scalar
   * non-string value is cast to a string. The one exception is boolean FALSE
   * which would normally become '' when cast to a string, but is manually
   * cast to '0' here for convenience and consistency.
   *
   * Any non-scalar value that is not an array (aka objects) gets cast
   * to an array.
   *
   * @param mixed $value
   *   A value being saved into the configuration system.
   *
   * @return string
   *   The value cast to a string or array.
   */
  public function castValue($value) {
    if (is_scalar($value) || $value === NULL) {
      // Handle special case of FALSE, which should be '0' instead of ''.
      if ($value === FALSE) {
        $value = '0';
      }
      else {
        $value = (string) $value;
      }
    }
    else {
      // Any non-scalar value must be an array.
      if (!is_array($value)) {
        $value = (array) $value;
      }
      // Recurse into any nested keys.
      foreach ($value as $key => $nested_value) {
        $value[$key] = $this->castValue($nested_value);
      }
    }
    return $value;
  }

  /**
   * Unsets value in this config object.
   *
   * @param string $key
   *   Name of the key whose value should be unset.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function clear($key) {
    if (!$this->isLoaded) {
      $this->load();
    }
    $parts = explode('.', $key);
    if (count($parts) == 1) {
      unset($this->data[$key]);
    }
    else {
      NestedArray::unsetValue($this->data, $parts);
    }
    $this->resetOverriddenData();
    return $this;
  }

  /**
   * Loads configuration data into this object.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function load() {
    $this->isLoaded = NULL;
    $data = $this->storage->read($this->name);
    if ($data === FALSE) {
      $this->isNew = TRUE;
      $this->setData(array());
    }
    else {
      $this->isNew = FALSE;
      $this->setData($data);
    }
    $this->notify('load');
    $this->isLoaded = TRUE;
    return $this;
  }

  /**
   * Saves the configuration object.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function save() {
    if (!$this->isLoaded) {
      $this->load();
    }
    $this->storage->write($this->name, $this->data);
    $this->isNew = FALSE;
    $this->notify('save');
    return $this;
  }

  /*
   * Renames the configuration object.
   *
   * @param $new_name
   *   The new name of the configuration object being constructed.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function rename($new_name) {
    if ($this->storage->rename($this->name, $new_name)) {
      $this->name = $new_name;
    }
    return $this;
  }

  /**
   * Deletes the configuration object.
   *
   * @return Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function delete() {
    // @todo Consider to remove the pruning of data for Config::delete().
    $this->data = array();
    $this->storage->delete($this->name);
    $this->isNew = TRUE;
    $this->resetOverriddenData();
    $this->notify('delete');
    return $this;
  }

  /**
   * Retrieve the storage used to load and save this configuration object.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The configuration storage object.
   */
  public function getStorage() {
    return $this->storage;
  }

  /**
   * Dispatch a config event.
   */
  protected function notify($config_event_name) {
    $this->eventDispatcher->dispatch('config.' . $config_event_name, new ConfigEvent($this));
  }

  /*
   * Merges data into a configuration object.
   *
   * @param array $data_to_merge
   *   An array containing data to merge.
   *
   * @return \Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function merge(array $data_to_merge) {
    if (!$this->isLoaded) {
      $this->load();
    }
    // Preserve integer keys so that config keys are not changed.
    $this->data = NestedArray::mergeDeepArray(array($this->data, $data_to_merge), TRUE);
    $this->resetOverriddenData();
    return $this;
  }
}
