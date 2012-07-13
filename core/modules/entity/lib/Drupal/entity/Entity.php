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
   * The property's data type plugin.
   *
   * @var \Drupal\Core\Property\PropertyTypeContainerInterface
   */
  protected $dataType;

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

  public function getRawValue($property_name, $langcode = NULL) {
    $langcode = isset($langcode) ? $langcode : $this->langcode;

    // Read $this->properties, possibly containing changes. If not set, return
    // the unchanged value from $this->values.
    if (isset($this->properties[$property_name][$langcode])) {
      $definition = $this->getPropertyDefinition($property_name);
      $data_type = drupal_get_property_type_plugin($definition['type']);
      return $data_type->getRawValue($definition, $this->properties[$property_name][$langcode]);
    }
    else {
      return isset($this->values[$property_name][$langcode]) ? $this->values[$property_name][$langcode] : NULL;
    }
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
      $data_type = drupal_get_property_type_plugin($definition['type']);

      $value = isset($this->values[$property_name][$langcode]) ? $this->values[$property_name][$langcode] : NULL;
      $this->properties[$property_name][$langcode] = $data_type->getProperty($definition, $value);
    }
    return $this->properties[$property_name][$langcode];
  }

  /**
   * Implements EntityInterface::set().
   */
  public function set($property_name, $value, $langcode = NULL) {
    $langcode = isset($langcode) ? $langcode : $this->langcode;

    // If a raw value is passed in, instantiate the object before setting.
    // @todo: Needs tests.
    if (!$value instanceof EntityPropertyInterface) {
      $definition = $this->getPropertyDefinition($property_name);
      $data_type = drupal_get_property_type_plugin($definition['type']);

      $value = $data_type->getProperty($definition, $value);
    }

    $this->properties[$property_name][$langcode] = $value;
  }

/*  public function __get($name) {
    return $this->get($name);
  }

  public function __set($name, $value) {
    $this->set($name, $value);
  }*/

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

  public function getIterator() {
    return new \ArrayIterator($this->getProperties());
  }

  public function getProperties() {
    $properties = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  public function getPropertyDefinitions() {
    // This is necessary as PDO writes values before calling __construct().
    // @todo: Fix and remove this.
    if (!isset($this->dataType)) {
      $this->dataType = drupal_get_property_type_plugin('entity');
    }

    return $this->dataType->getPropertyDefinitions(array(
      'type' => 'entity',
      'entity type' => $this->entityType,
      'bundle' => $this->bundle(),
    ));
  }

  public function toArray() {
    $value = array();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      $value[$name] = $this->getRawValue($name);
    }
    return $value;
  }

  public function access($account) {
    // TODO: Implement access() method.
  }

  public function validate() {
    // TODO: Implement validate() method.
  }
}
