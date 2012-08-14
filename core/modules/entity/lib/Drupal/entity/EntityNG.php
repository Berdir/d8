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
   * Overrides Entity::id().
   */
  public function id() {
    return $this->get('id')->value;
  }

  /**
   * Implements EntityInterface::get().
   */
  public function get($property_name, $langcode = NULL) {
    // Values in default language are stored using LANGUAGE_NOT_SPECIFIED.
    if (!isset($langcode) || (isset($langcode) && $langcode == $this->get('language')->langcode)) {
      // @todo: Find a more meaningful constant name and make field loading use
      // it too.
      $langcode = LANGUAGE_NOT_SPECIFIED;
    }

    // Populate $this->properties to fasten further lookups and to keep track of
    // property objects, possibly holding changes to properties.
    if (!isset($this->properties[$property_name][$langcode])) {
      $definition = $this->getPropertyDefinition($property_name);
      if (!$definition) {
        throw new \InvalidArgumentException('Property ' . check_plain($property_name) . ' is unknown.');
      }

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
   * Magic getter: Gets the property in default language.
   */
  public function __get($name) {
    if ($this->getPropertyDefinition($name)) {
      return $this->get($name);
    }
    return isset($this->$name) ? $this->$name : NULL;
  }

  /**
   * Magic getter: Sets the property in default language.
   */
  public function __set($name, $value) {
    if ($this->getPropertyDefinition($name)) {
      $this->set($name, $value);
    }
    else {
      $this->$name = $value;
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
   * Gets a translation of the entity.
   *
   * @return \Drupal\Core\Property\PropertyContainerInterface
   *   A container holding the translated properties.
   */
  public function getTranslation($langcode) {

    if ($langcode == $this->get('language')->langcode) {
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
}
