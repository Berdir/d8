<?php

/**
 * @file
 * Definition of Drupal\entity\EntityNG.
 */

namespace Drupal\entity;

use Drupal\Core\Property\PropertyInterface;
use Drupal\Core\Property\PropertyContainerInterface;

/**
 * Implements Property API specific enhancements to the Entity class.
 *
 * @todo: Once all entity types have been converted, merge improvements into the
 * Entity class and let EntityInterface extend the PropertyContainerInterface.
 */
class EntityNG extends Entity implements PropertyContainerInterface {

  /**
   * The plain data values of the contained properties.
   *
   * This always holds the original, unchanged values of the entity. The values
   * are keyed by language code, whereas LANGUAGE_NOT_SPECIFIED is used for
   * values in default language.
   *
   * @todo: Add methods for getting original properties and for determining
   * changes.
   *
   * @var array
   */
  protected $values = array();

  /**
   * The array of properties, each being an instance of EntityPropertyInterface.
   *
   * @var array
   */
  protected $properties = array();

  /**
   * Whether the entity is in pre-Property API compatibility mode.
   *
   * If set to TRUE, property values are written directly to $this->values, thus
   * must be plain property values keyed by language code. This must be enabled
   * when calling field API attachers.
   *
   * @var bool
   */
  protected $compatibilityMode = FALSE;


  /**
   * Overrides Entity::id().
   */
  public function id() {
    return $this->get('id')->value;
  }

  /**
   * Implements EntityInterface::get().
   */
  public function get($property_name, $langcode = NULL) {
    // Values in default language are stored using LANGUAGE_NOT_SPECIFIED,
    // so use LANGUAGE_NOT_SPECIFIED if either no language is given or it
    // matches the default language. Then, if the default language is
    // LANGUAGE_NOT_SPECIFIED, the entity is not translatable, so we always use
    // LANGUAGE_NOT_SPECIFIED.
    if (!isset($langcode) || $langcode == $this->language->langcode || LANGUAGE_NOT_SPECIFIED == $this->language->langcode) {
      // @todo: Find a more meaningful constant name and make field loading use
      // it too.
      $langcode = LANGUAGE_NOT_SPECIFIED;
    }
    else {
      $languages = language_list(LANGUAGE_ALL);
      if (!isset($languages[$langcode])) {
        throw new \InvalidArgumentException("Unable to get translation for the invalid language '$langcode'.");
      }
    }

    // Populate $this->properties to fasten further lookups and to keep track of
    // property objects, possibly holding changes to properties.
    if (!isset($this->properties[$property_name][$langcode])) {
      $definition = $this->getPropertyDefinition($property_name);
      if (!$definition) {
        throw new \InvalidArgumentException('Property ' . check_plain($property_name) . ' is unknown.');
      }
      // Non-translatable properties always use LANGUAGE_NOT_SPECIFIED.
      $langcode = empty($definition['translatable']) ? LANGUAGE_NOT_SPECIFIED : $langcode;

      $value = isset($this->values[$property_name][$langcode]) ? $this->values[$property_name][$langcode] : NULL;
      $this->properties[$property_name][$langcode] = drupal_get_property($definition, $value);
    }
    return $this->properties[$property_name][$langcode];
  }

  /**
   * Implements EntityInterface::set().
   */
  public function set($property_name, $value, $langcode = NULL) {
    $value = $value instanceof PropertyInterface ? $value->getValue() : $value;
    $this->get($property_name, $langcode)->setValue($value);
  }

  /**
   * Implements PropertyContainerInterface::getProperties().
   */
  public function getProperties() {
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if (empty($definition['computed'])) {
        $properties[$name] = $this->get($name);
      }
    }
    return $properties;
  }

  /**
   * Implements PropertyContainerInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($properties as $name => $property) {
      // Copy the value to our property object.
      $value = $property instanceof PropertyInterface ? $property->getValue() : $property;
      $this->get($name)->setValue($value);
    }
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new \ArrayIterator($this->getProperties());
  }

  /**
   * Implements PropertyContainerInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements PropertyContainerInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    return entity_get_controller($this->entityType)->getPropertyDefinitions(array(
      'entity type' => $this->entityType,
      'bundle' => $this->bundle(),
    ));
  }

  /**
   * Implements PropertyContainerInterface::toArray().
   */
  public function toArray() {
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements EntityInterface::language().
   */
  public function language() {
    // @todo: Check for language.module instead, once Field API language
    // handling depends upon it too.
    return module_exists('locale') ? $this->language->object : FALSE;
  }

  /**
   * Gets a translation of the entity.
   *
   * @return \Drupal\Core\Property\PropertyContainerInterface
   *   A container holding the translated properties.
   */
  public function getTranslation($langcode) {

    if ($langcode == LANGUAGE_NOT_SPECIFIED || $langcode == $this->get('language')->langcode) {
      // No translation need, return the entity.
      return $this;
    }
    // Check whether the language code is valid, thus is of an available
    // language.
    $languages = language_list(LANGUAGE_ALL);
    if (!isset($languages[$langcode])) {
      throw new \InvalidArgumentException("Unable to get translation for the invalid language '$langcode'.");
    }

    $definition = array(
      'entity type' => $this->entityType,
      'bundle' => $this->bundle(),
      'langcode' => $langcode,
    );
    return new EntityTranslation($definition, NULL, array('parent' => $this));
  }

  /**
   * Overrides EntityInterface::translations().
   */
  public function translations() {
    $translations = array();
    // Build an array with the translation langcodes set as keys.
    foreach ($this->getProperties() as $name => $property) {
      if (isset($this->values[$name])) {
        $translations += $this->values[$name];
      }
      $translations += $this->properties[$name];
    }
    unset($translations[LANGUAGE_NOT_SPECIFIED]);

    // Now get languages based upon translation langcodes.
    $languages = array_intersect_key(language_list(), $translations);
    return $languages;
  }

  public function access($account) {
    // TODO: Implement access() method.
  }

  /**
   * Enables or disable the compatibility mode.
   *
   * @param bool $enabled
   *   Whether to enable the mode.
   *
   * @see EntityNG::compatibilityMode
   */
  public function setCompatibilityMode($enabled) {
    $this->compatibilityMode = (bool) $enabled;
  }

  /**
   * Returns whether the compatibility mode is active.
   */
  public function getCompatibilityMode() {
    return $this->compatibilityMode;
  }

  /**
   * Updates the original values with the interim changes.
   *
   * Note: This should be called by the storage controller during a save
   * operation.
   */
  public function updateOriginalValues() {
    foreach ($this->properties as $name => $properties) {
      foreach ($properties as $langcode => $property) {
        $this->values[$name][$langcode] = $property->getValue();
      }
    }
  }

  /**
   * Magic getter: Gets the property in default language.
   *
   * For compatibility mode to work this must return a reference.
   */
  public function &__get($name) {
    if ($this->compatibilityMode) {
      if (!isset($this->values[$name])) {
        $this->values[$name] = NULL;
      }
      return $this->values[$name];
    }
    if ($this->getPropertyDefinition($name)) {
      $return = $this->get($name);
      return $return;
    }
    if (!isset($this->$name)) {
      $this->$name = NULL;
    }
    return $this->$name;
  }

  /**
   * Magic getter: Sets the property in default language.
   */
  public function __set($name, $value) {
    if ($this->compatibilityMode) {
      $this->values[$name] = $value;
    }
    elseif ($this->getPropertyDefinition($name)) {
      $this->set($name, $value);
    }
    else {
      $this->$name = $value;
    }
  }

  /**
   * Magic method.
   */
  public function __isset($name) {
    if ($this->compatibilityMode) {
      return isset($this->values[$name]);
    }
    else {
      return isset($this->properties[$name]);
    }
  }

  /**
   * Magic method.
   */
  public function __unset($name) {
    if ($this->compatibilityMode) {
      unset($this->values[$name]);
    }
    else {
      unset($this->properties[$name]);
    }
  }
}
