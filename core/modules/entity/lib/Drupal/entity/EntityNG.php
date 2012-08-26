<?php

/**
 * @file
 * Definition of Drupal\entity\EntityNG.
 */

namespace Drupal\entity;

use Drupal\Core\TypedData\DataWrapperInterface;
use Drupal\Core\TypedData\DataStructureTranslatableInterface;
use Drupal\Core\TypedData\DataAccessibleInterface;
use Drupal\Component\Uuid\Uuid;
use ArrayIterator;
use InvalidArgumentException;

/**
 * Implements Property API specific enhancements to the Entity class.
 *
 * @todo: Once all entity types have been converted, merge improvements into the
 * Entity class and let EntityInterface extend the DataStructureInterface.
 */
class EntityNG extends Entity implements DataStructureTranslatableInterface, DataAccessibleInterface {

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
   * The array of properties, each being an instance of
   * EntityPropertyListInterface.
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
   * Overrides Entity::uuid().
   */
  public function uuid() {
    return $this->get('uuid')->value;
  }

  /**
   * Implements EntityInterface::get().
   */
  public function get($property_name, $langcode = NULL) {
    // Values in default language are stored using the LANGUAGE_DEFAULT
    // constant. If the default language is LANGUAGE_NOT_SPECIFIED, the entity
    // is not translatable, so we always use LANGUAGE_DEFAULT.
    if (!isset($langcode) || $langcode == $this->langcode->value || LANGUAGE_NOT_SPECIFIED == $this->langcode->value) {
      $langcode = LANGUAGE_DEFAULT;
    }
    else {
      $languages = language_list(LANGUAGE_ALL);
      if (!isset($languages[$langcode])) {
        throw new InvalidArgumentException("Unable to get translation for the invalid language '$langcode'.");
      }
    }

    // Populate $this->properties to fasten further lookups and to keep track of
    // property objects, possibly holding changes to properties.
    if (!isset($this->properties[$property_name][$langcode])) {
      $definition = $this->getPropertyDefinition($property_name);
      if (!$definition) {
        throw new InvalidArgumentException('Property ' . check_plain($property_name) . ' is unknown.');
      }
      // Non-translatable properties always use default language.
      $langcode = empty($definition['translatable']) ? LANGUAGE_DEFAULT : $langcode;

      $value = isset($this->values[$property_name][$langcode]) ? $this->values[$property_name][$langcode] : NULL;
      $this->properties[$property_name][$langcode] = drupal_wrap_data($definition, $value);
    }
    return $this->properties[$property_name][$langcode];
  }

  /**
   * Implements EntityInterface::set().
   */
  public function set($property_name, $value, $langcode = NULL) {
    $value = $value instanceof DataWrapperInterface ? $value->getValue() : $value;
    $this->get($property_name, $langcode)->setValue($value);
  }

  /**
   * Implements DataStructureInterface::getProperties().
   */
  public function getProperties($include_computed = FALSE) {
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if ($include_computed || empty($definition['computed'])) {
        $properties[$name] = $this->get($name);
      }
    }
    return $properties;
  }

  /**
   * Implements DataStructureInterface::setProperties().
   */
  public function setProperties($properties) {
    foreach ($properties as $name => $property) {
      // Copy the value to our property object.
      $value = $property instanceof DataWrapperInterface ? $property->getValue() : $property;
      $this->get($name)->setValue($value);
    }
  }

  /**
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new ArrayIterator($this->getProperties());
  }

  /**
   * Implements DataStructureInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  /**
   * Implements DataStructureInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    return entity_get_controller($this->entityType)->getPropertyDefinitions(array(
      'type' => 'entity',
      'entity type' => $this->entityType,
      'bundle' => $this->bundle(),
    ));
  }

  /**
   * Implements DataStructureInterface::toArray().
   */
  public function toArray() {
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements DataStructureTranslatableInterface::language().
   */
  public function language() {
    return $this->langcode->language;
  }

  /**
   * Implements DataStructureTranslatableInterface::getTranslation().
   */
  public function getTranslation($langcode) {

    if ($langcode == LANGUAGE_DEFAULT || $langcode == $this->language()->langcode) {
      // No translation needed, return the entity.
      return $this;
    }
    // Check whether the language code is valid, thus is of an available
    // language.
    $languages = language_list(LANGUAGE_ALL);
    if (!isset($languages[$langcode])) {
      throw new InvalidArgumentException("Unable to get translation for the invalid language '$langcode'.");
    }
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      $properties[$name] = $this->get($name, $langcode);
    }
    $translation_definition = array(
      'type' => 'entity_translation',
      'entity type' => $this->entityType(),
      'bundle' => $this->bundle(),
    );
    return drupal_wrap_data($translation_definition, $properties, array('parent' => $this, 'langcode' => $langcode));
  }

  /**
   * Implements DataStructureTranslatableInterface::getTranslationLanguages().
   */
  public function getTranslationLanguages($include_default = TRUE) {
    $translations = array();
    // Build an array with the translation langcodes set as keys.
    foreach ($this->getProperties() as $name => $property) {
      if (isset($this->values[$name])) {
        $translations += $this->values[$name];
      }
      $translations += $this->properties[$name];
    }
    unset($translations[LANGUAGE_DEFAULT]);

    if ($include_default) {
      $translations[$this->language()->langcode] = TRUE;
    }

    // Now get languages based upon translation langcodes.
    $languages = array_intersect_key(language_list(LANGUAGE_ALL), $translations);
    return $languages;
  }

  /**
   * Implements DataAccessibleInterface::access().
   */
  public function access(\Drupal\user\User $account = NULL) {
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

  /**
   * Overrides Entity::createDuplicate().
   */
  public function createDuplicate() {
    $duplicate = clone $this;
    $entity_info = $this->entityInfo();
    $this->{$entity_info['entity keys']['id']}->value = NULL;

    // Check if the entity type supports UUIDs and generate a new one if so.
    if (!empty($entity_info['entity keys']['uuid'])) {
      $uuid = new Uuid();
      $duplicate->{$entity_info['entity keys']['uuid']}->value = $uuid->generate();
    }
    return $duplicate;
  }

  /**
   * Implements a deep clone.
   */
  public function __clone() {
    foreach ($this->properties as $name => $properties) {
      foreach ($properties as $langcode => $property) {
        $this->properties[$name][$langcode] = clone $property;
      }
    }
  }
}

