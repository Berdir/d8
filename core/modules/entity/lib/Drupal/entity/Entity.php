<?php

/**
 * @file
 * Definition of Drupal\entity\Entity.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyTypeContainerInterface;
use Drupal\Core\Property\PropertyContainerInterface;

/**
 * Defines a base entity class.
 *
 * Default implementation of EntityInterface.
 *
 * This class can be used as-is by simple entity types. Entity types requiring
 * special handling can extend the class.
 */
class Entity implements EntityInterface {

  /**
   * The language code of the entity's default language.
   *
   * @var string
   */
  public $langcode = LANGUAGE_NOT_SPECIFIED;

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Boolean indicating whether the entity should be forced to be new.
   *
   * @var bool
   */
  protected $enforceIsNew;

  /**
   * The raw data values of the contained properties.
   *
   * This always holds the original, unchanged values of the entity.
   *
   * @todo: Add methods for getting original properties and to see what has
   * changed.
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

  /*
   * Indicates whether this is the current revision.
   *
   * @var bool
   */
  public $isCurrentRevision = TRUE;

  /**
   * Constructs a new entity object.
   */
  public function __construct(array $values, $entity_type) {
    $this->entityType = $entity_type;
    // Set initial values.
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * Implements EntityInterface::id().
   */
  public function id() {
    return isset($this->id) ? $this->id : NULL;
  }

  /**
   * Implements EntityInterface::isNew().
   */
  public function isNew() {
    return !empty($this->enforceIsNew) || !$this->id();
  }

  /**
   * Implements EntityInterface::enforceIsNew().
   */
  public function enforceIsNew($value = TRUE) {
    $this->enforceIsNew = $value;
  }

  /**
   * Implements EntityInterface::entityType().
   */
  public function entityType() {
    return $this->entityType;
  }

  /**
   * Implements EntityInterface::bundle().
   */
  public function bundle() {
    return $this->entityType;
  }

  /**
   * Implements EntityInterface::label().
   */
  public function label($langcode = NULL) {
    $label = FALSE;
    $entity_info = $this->entityInfo();
    if (isset($entity_info['label callback']) && function_exists($entity_info['label callback'])) {
      $label = $entity_info['label callback']($this->entityType, $this, $langcode);
    }
    elseif (!empty($entity_info['entity keys']['label']) && isset($this->{$entity_info['entity keys']['label']})) {
      $label = $this->{$entity_info['entity keys']['label']};
    }
    return $label;
  }

  /**
   * Implements EntityInterface::uri().
   *
   * @see entity_uri()
   */
  public function uri() {
    $bundle = $this->bundle();
    // A bundle-specific callback takes precedence over the generic one for the
    // entity type.
    $entity_info = $this->entityInfo();
    if (isset($entity_info['bundles'][$bundle]['uri callback'])) {
      $uri_callback = $entity_info['bundles'][$bundle]['uri callback'];
    }
    elseif (isset($entity_info['uri callback'])) {
      $uri_callback = $entity_info['uri callback'];
    }
    else {
      return NULL;
    }

    // Invoke the callback to get the URI. If there is no callback, return NULL.
    if (isset($uri_callback) && function_exists($uri_callback)) {
      $uri = $uri_callback($this);
      // Pass the entity data to url() so that alter functions do not need to
      // look up this entity again.
      $uri['options']['entity_type'] = $this->entityType;
      $uri['options']['entity'] = $this;
      return $uri;
    }
  }

  /**
   * Implements EntityInterface::language().
   */
  public function language() {
    // @todo: Check for language.module instead, once Field API language
    // handling depends upon it too.
    return module_exists('locale') ? language_load($this->langcode) : FALSE;
  }

  /**
   * Implements EntityInterface::translations().
   */
  public function translations() {
    $languages = array();
    $entity_info = $this->entityInfo();
    if ($entity_info['fieldable'] && ($default_language = $this->language())) {
      // Go through translatable properties and determine all languages for
      // which translated values are available.
      foreach (field_info_instances($this->entityType, $this->bundle()) as $field_name => $instance) {
        $field = field_info_field($field_name);
        if (field_is_translatable($this->entityType, $field) && isset($this->$field_name)) {
          foreach ($this->$field_name as $langcode => $value)  {
            $languages[$langcode] = TRUE;
          }
        }
      }
      // Remove the default language from the translations.
      unset($languages[$default_language->langcode]);
      $languages = array_intersect_key(language_list(), $languages);
    }
    return $languages;
  }

  /**
   * Implements EntityInterface::get().
   */
  public function get($property_name, $langcode = NULL) {
    $langcode = isset($langcode) ? $langcode : $this->langcode;

    // Populate $this->properties to fasten further lookups and to keep track of
    // property objects, possibly holding changes to properties.
    if (!isset($this->properties[$property_name][$langcode])) {
      $definition = $this->getPropertyDefinition($property_name);
      $value = isset($this->values[$property_name][$langcode]) ? $this->values[$property_name][$langcode] : NULL;

      $this->properties[$property_name][$langcode] = drupal_get_property($definition, $value);
    }
    return $this->properties[$property_name][$langcode];
  }

  /**
   * Implements EntityInterface::set().
   */
  public function set($property_name, $value, $langcode = NULL) {
    $this->get($property_name, $langcode)->setValue($value);
  }

  /**
   * Implements EntityInterface::save().
   */
  public function save() {
    return entity_get_controller($this->entityType)->save($this);
  }

  /**
   * Implements EntityInterface::delete().
   */
  public function delete() {
    if (!$this->isNew()) {
      entity_get_controller($this->entityType)->delete(array($this->id()));
    }
  }

  /**
   * Implements EntityInterface::createDuplicate().
   */
  public function createDuplicate() {
    $duplicate = clone $this;
    $duplicate->id = NULL;
    return $duplicate;
  }

  /**
   * Implements EntityInterface::entityInfo().
   */
  public function entityInfo() {
    return entity_get_info($this->entityType);
  }

  /**
   * Implements Drupal\entity\EntityInterface::getRevisionId().
   */
  public function getRevisionId() {
    return NULL;
  }

  /**
   * Implements Drupal\entity\EntityInterface::isCurrentRevision().
   */
  public function isCurrentRevision() {
    return $this->isCurrentRevision;
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
    $definitions = $this->getPropertyDefinitions();
    foreach ($properties as $name => $property) {
      if (isset($definitions[$name])) {
        $this->properties[$name] = $property;
      }
      // @todo: Throw exception else, invalid properties given?.
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

  public function access($account) {
    // TODO: Implement access() method.
  }
}
