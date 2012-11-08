<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\EntityNG.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\TypedData\ContextAwareInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Component\Uuid\Uuid;
use ArrayIterator;
use InvalidArgumentException;

/**
 * Implements Entity Field API specific enhancements to the Entity class.
 *
 * An entity implements the ComplexDataInterface, thus is complex data
 * containing fields as its data properties. The entity fields have to implement
 * the \Drupal\Core\Entity\Field\FieldInterface.
 *
 * @todo: Once all entity types have been converted, merge improvements into the
 * Entity class and overhaul the EntityInterface.
 */
class EntityNG extends Entity {

  /**
   * The plain data values of the contained fields.
   *
   * This always holds the original, unchanged values of the entity. The values
   * are keyed by language code, whereas LANGUAGE_NOT_SPECIFIED is used for
   * values in default language.
   *
   * @todo: Add methods for getting original fields and for determining
   * changes.
   * @todo: Provide a better way for defining default values.
   *
   * @var array
   */
  protected $values = array(
    'langcode' => array(LANGUAGE_DEFAULT => array(0 => array('value' => LANGUAGE_NOT_SPECIFIED))),
  );

  /**
   * The array of fields, each being an instance of FieldInterface.
   *
   * @var array
   */
  protected $fields = array();

  /**
   * An instance of the backward compatibility decorator.
   *
   * @var EntityBCDecorator
   */
  protected $bcEntity;

  /**
   * Static cache for property definitions.
   *
   * @var array
   */
  protected $fieldDefinitions;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct(array(), $entity_type);
    $this->values = $values + $this->values;
    $this->init();
  }

  /**
   * Initialize the object. Invoked upon construction and wake up.
   */
  protected function init() {
    // We unset all defined properties, so magic getters apply.
    unset($this->langcode);
  }

  /**
   * Magic __wakeup() implemenation.
   */
  public function __wakeup() {
    $this->init();
  }

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
   * Implements ComplexDataInterface::get().
   */
  public function get($property_name) {
    // Values in default language are always stored using the LANGUAGE_DEFAULT
    // constant.
    if (!isset($this->fields[$property_name][LANGUAGE_DEFAULT])) {
      return $this->getTranslatedField($property_name, LANGUAGE_DEFAULT);
    }
    return $this->fields[$property_name][LANGUAGE_DEFAULT];
  }

  /**
   * Gets a translated field.
   *
   * @return \Drupal\Core\Entity\Field\FieldInterface
   */
  protected function getTranslatedField($property_name, $langcode) {
    // Populate $this->properties to fasten further lookups and to keep track of
    // property objects, possibly holding changes to properties.
    if (!isset($this->fields[$property_name][$langcode])) {
      $definition = $this->getPropertyDefinition($property_name);
      if (!$definition) {
        throw new InvalidArgumentException('Field ' . check_plain($property_name) . ' is unknown.');
      }
      // Non-translatable properties always use default language.
      if ($langcode != LANGUAGE_DEFAULT && empty($definition['translatable'])) {
        $this->fields[$property_name][$langcode] = $this->getTranslatedField($property_name, LANGUAGE_DEFAULT);
      }
      else {
        $value = isset($this->values[$property_name][$langcode]) ? $this->values[$property_name][$langcode] : NULL;
        $context = array('parent' => $this, 'name' => $property_name);
        $this->fields[$property_name][$langcode] = typed_data()->create($definition, $value, $context);
      }
    }
    return $this->fields[$property_name][$langcode];
  }

  /**
   * Implements ComplexDataInterface::set().
   */
  public function set($property_name, $value) {
    $this->get($property_name)->setValue($value);
  }

  /**
   * Implements ComplexDataInterface::getProperties().
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
   * Implements IteratorAggregate::getIterator().
   */
  public function getIterator() {
    return new ArrayIterator($this->getProperties());
  }

  /**
   * Implements ComplexDataInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    // Try to read from static cache.
    if (isset($this->fieldDefinitions)) {
      return isset($this->fieldDefinitions[$name]) ? $this->fieldDefinitions[$name] : FALSE;
    }

    // First try getting property definitions which apply to all entities of
    // this type. Then if this fails add in definitions of optional properties
    // as well. That way we can use property definitions of base properties
    // when determining the optional properties of an entity.
    $definitions = entity_get_controller($this->entityType)->getFieldDefinitions(array());

    if (isset($definitions[$name])) {
      return $definitions[$name];
    }
    // Add in optional properties if any.
    if ($definitions = $this->getPropertyDefinitions()) {
      return isset($definitions[$name]) ? $definitions[$name] : FALSE;
    }
  }

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    if (!isset($this->fieldDefinitions)) {
      $this->fieldDefinitions = entity_get_controller($this->entityType)->getFieldDefinitions(array(
        'entity type' => $this->entityType,
        'bundle' => $this->bundle(),
      ));
    }
    return $this->fieldDefinitions;
  }

  /**
   * Implements ComplexDataInterface::getPropertyValues().
   */
  public function getPropertyValues() {
    $values = array();
    foreach ($this->getProperties() as $name => $property) {
      $values[$name] = $property->getValue();
    }
    return $values;
  }

  /**
   * Implements ComplexDataInterface::setPropertyValues().
   */
  public function setPropertyValues($values) {
    foreach ($values as $name => $value) {
      $this->get($name)->setValue($value);
    }
  }

  /**
   * Implements ComplexDataInterface::isEmpty().
   */
  public function isEmpty() {
    if (!$this->isNew()) {
      return FALSE;
    }
    foreach ($this->getProperties() as $property) {
      if ($property->getValue() !== NULL) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Implements TranslatableInterface::language().
   */
  public function language() {
    $language = $this->get('langcode')->language;
    if (!$language) {
      // Make sure we return a proper language object.
      // @todo Refactor this, see: http://drupal.org/node/1834542.
      $language = language_default();
    }
    return $language;
  }

  /**
   * Implements TranslatableInterface::getTranslation().
   *
   * @return \Drupal\Core\Entity\Field\Type\EntityTranslation
   */
  public function getTranslation($langcode, $strict = TRUE) {
    // If the default language is LANGUAGE_NOT_SPECIFIED, the entity is not
    // translatable, so we use LANGUAGE_DEFAULT.
    if ($langcode == LANGUAGE_DEFAULT || in_array($this->language()->langcode, array(LANGUAGE_NOT_SPECIFIED, $langcode))) {
      // No translation needed, return the entity.
      return $this;
    }
    // Check whether the language code is valid, thus is of an available
    // language.
    $languages = language_list(LANGUAGE_ALL);
    if (!isset($languages[$langcode])) {
      throw new InvalidArgumentException("Unable to get translation for the invalid language '$langcode'.");
    }
    $fields = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      // Load only translatable properties in strict mode.
      if (!empty($definition['translatable']) || !$strict) {
        $fields[$name] = $this->getTranslatedField($name, $langcode);
      }
    }
    $translation_definition = array(
      'type' => 'entity_translation',
      'constraints' => array(
        'entity type' => $this->entityType(),
        'bundle' => $this->bundle(),
      ),
    );
    $translation = typed_data()->create($translation_definition, $fields, array(
      'parent' => $this,
      'name' => $langcode,
    ));
    $translation->setStrictMode($strict);
    return $translation;
  }

  /**
   * Implements TranslatableInterface::getTranslationLanguages().
   */
  public function getTranslationLanguages($include_default = TRUE) {
    $translations = array();
    // Build an array with the translation langcodes set as keys.
    foreach ($this->getProperties() as $name => $property) {
      if (isset($this->values[$name])) {
        $translations += $this->values[$name];
      }
      $translations += $this->fields[$name];
    }
    unset($translations[LANGUAGE_DEFAULT]);

    if ($include_default) {
      $translations[$this->language()->langcode] = TRUE;
    }

    // Now get languages based upon translation langcodes. Empty languages must
    // be filtered out as they concern empty/unset properties.
    $languages = array_intersect_key(language_list(LANGUAGE_ALL), array_filter($translations));
    return $languages;
  }

  /**
   * Overrides Entity::translations().
   *
   * @todo: Remove once Entity::translations() gets removed.
   */
  public function translations() {
    return $this->getTranslationLanguages(FALSE);
  }

  /**
   * Overrides Entity::getBCEntity().
   */
  public function getBCEntity() {
    if (!isset($this->bcEntity)) {
      $this->bcEntity = new EntityBCDecorator($this);
    }
    return $this->bcEntity;
  }

  /**
   * Updates the original values with the interim changes.
   */
  public function updateOriginalValues() {
    if (!$this->fields) {
      return;
    }
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if (empty($definition['computed']) && !empty($this->fields[$name])) {
        foreach ($this->fields[$name] as $langcode => $field) {
          $this->values[$name][$langcode] = $field->getValue();
        }
      }
    }
  }

  /**
   * Magic getter: Gets the property in default language.
   *
   * For compatibility mode to work this must return a reference.
   */
  public function &__get($name) {
    if (isset($this->fields[$name][LANGUAGE_DEFAULT])) {
      return $this->fields[$name][LANGUAGE_DEFAULT];
    }
    if ($this->getPropertyDefinition($name)) {
      $return = $this->get($name);
      return $return;
    }
    // Allow the EntityBCDecorator to directly access the values and fields.
    // @todo: Remove once the EntityBCDecorator gets removed.
    if ($name == 'values' || $name == 'fields') {
      return $this->$name;
    }
    // Else directly read/write plain values. That way, fields not yet converted
    // to the entity field API can always be accessed as in compatibility mode.
    if (!isset($this->values[$name])) {
      $this->values[$name] = NULL;
    }
    return $this->values[$name];
  }

  /**
   * Magic getter: Sets the property in default language.
   */
  public function __set($name, $value) {
    // Support setting values via property objects.
    if ($value instanceof TypedDataInterface) {
      $value = $value->getValue();
    }

    if (isset($this->fields[$name][LANGUAGE_DEFAULT])) {
      $this->fields[$name][LANGUAGE_DEFAULT]->setValue($value);
    }
    elseif ($this->getPropertyDefinition($name)) {
      $this->get($name)->setValue($value);
    }
    // Else directly read/write plain values. That way, fields not yet converted
    // to the entity field API can always be accessed as in compatibility mode.
    else {
      $this->values[$name] = $value;
    }
  }

  /**
   * Magic method.
   */
  public function __isset($name) {
    if ($this->getPropertyDefinition($name)) {
      return (bool) count($this->get($name));
    }
    else {
      return isset($this->values[$name]);
    }
  }

  /**
   * Magic method.
   */
  public function __unset($name) {
    if ($this->getPropertyDefinition($name)) {
      $this->get($name)->setValue(array());
    }
    else {
      unset($this->values[$name]);
    }
  }

  /**
   * Overrides Entity::createDuplicate().
   */
  public function createDuplicate() {
    $duplicate = clone $this;
    $entity_info = $this->entityInfo();
    $duplicate->{$entity_info['entity_keys']['id']}->value = NULL;

    // Check if the entity type supports UUIDs and generate a new one if so.
    if (!empty($entity_info['entity_keys']['uuid'])) {
      $uuid = new Uuid();
      $duplicate->{$entity_info['entity_keys']['uuid']}->value = $uuid->generate();
    }
    return $duplicate;
  }

  /**
   * Implements a deep clone.
   */
  public function __clone() {
    $this->bcEntity = NULL;

    foreach ($this->fields as $name => $properties) {
      foreach ($properties as $langcode => $property) {
        $this->fields[$name][$langcode] = clone $property;
        if ($property instanceof ContextAwareInterface) {
          $this->fields[$name][$langcode]->setParent($this);
        }
      }
    }
  }

  /**
   * Overrides Entity::label() to access the label field with the new API.
   */
  public function label($langcode = NULL) {
    $label = NULL;
    $entity_info = $this->entityInfo();
    if (isset($entity_info['label_callback']) && function_exists($entity_info['label_callback'])) {
      $label = $entity_info['label_callback']($this->entityType, $this, $langcode);
    }
    elseif (!empty($entity_info['entity_keys']['label']) && isset($this->{$entity_info['entity_keys']['label']})) {
      $label = $this->{$entity_info['entity_keys']['label']}->value;
    }
    return $label;
  }
}
