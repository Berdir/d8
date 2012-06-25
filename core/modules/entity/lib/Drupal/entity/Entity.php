<?php

/**
 * @file
 * Definition of Drupal\entity\Entity.
 */

namespace Drupal\entity;
use \Drupal\Core\Property\PropertyTypeContainerInterface;
use \Drupal\Core\Property\PropertyContainerInterface;

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
   * @var array
   */
  protected $values = array();

  /**
   * The property's data type plugin.
   *
   * @var \Drupal\Core\Property\PropertyTypeContainerInterface
   */
  protected $dataType;


  /**
   * Constructs a new entity object.
   */
  public function __construct(array $values, $entity_type) {
    $this->entityType = $entity_type;
    // Set initial values.
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }

    // @todo: Use dependency injection.
    $this->dataType = drupal_get_property_type_plugin('entity');
    $this->values = $values;

    // Set up initial references for primitives upon creation.
    $data_types = drupal_get_data_type_info();
    foreach ($this->getPropertyDefinitions() as $name => $definition) {
      if (!($data_types[$definition['type']]['class'] instanceof PropertyTypeContainerInterface)) {

        if (!isset($this->values[$name])) {
          $this->values[$name] = NULL;
        }
        $this->$name = & $this->values[$name];
      }
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
   *
   * @see entity_label()
   */
  public function label() {
    $label = FALSE;
    $entity_info = $this->entityInfo();
    if (isset($entity_info['label callback']) && function_exists($entity_info['label callback'])) {
      $label = $entity_info['label callback']($this->entityType, $this);
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
    return isset($this->values[$property_name][$langcode]) ? $this->values[$property_name][$langcode] : NULL;
  }

  /**
   * Implements EntityInterface::get().
   */
  public function get($property_name, $langcode = NULL) {
    // @todo: What about possible name clashes?
    if (!property_exists($this, $property_name) || isset($langcode)) {

      $langcode = isset($langcode) ? $langcode : $this->langcode;
      $value_ref = & $this->values[$property_name][$langcode];

      // Primitive properties already exist, so this must be a property
      // container. @see self::__construct()
      if ($definition = $this->dataType->getPropertyDefinition($property_name)) {
        $this->$property_name = drupal_get_property_type_plugin($definition['type'])->createItem($definition, $value_ref);
      }
      // Add BC for not yet converted stuff.
      else {
        $this->$property_name = & $this->values[$property_name];
      }
    }
    return $this->$property_name;
  }

  /**
   * Implements EntityInterface::set().
   */
  public function set($property_name, $value, $langcode = NULL) {
    $definition = $this->dataType->getPropertyDefinition($property_name);

    // Add BC for not yet converted stuff.
    if (!$definition) {
      $this->values[$property_name] = $value;
      $this->$property_name = & $this->values[$property_name];
      return;
    }

    $data_type = drupal_get_property_type_plugin($definition['type']);
    $langcode = isset($langcode) ? $langcode : $this->langcode;
    $value_ref = & $this->values[$property_name][$langcode];

    if ($data_type instanceof PropertyTypeContainerInterface) {
      // Transform container objects back to raw values before setting if
      // necessary. Support passing in raw values as well.
      // @todo: Needs tests.
      if ($value instanceof PropertyContainerInterface) {
        $value = $data_type->getRawValue($definition, $value);
      }
      $value_ref = $value;
      unset($this->$property_name);
    }
    else {
      // Just update the internal value. $this->$name is a reference on it, so
      // it will automatically reflect the update too.
      $value_ref = $value;
    }
  }

  public function __get($name) {
    return $this->get($name);
  }

  public function __set($name, $value) {
    $this->set($name, $value);
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

  public function getIterator() {
    // TODO: Implement getIterator() method.
  }

  public function getProperties() {
    // TODO: Implement getProperties() method.
  }

  public function getPropertyDefinition($name) {
    $definitions = $this->getPropertyDefinitions();
    return isset($definitions[$name]) ? $definitions[$name] : FALSE;
  }

  public function getPropertyDefinitions() {
    return $this->dataType->getPropertyDefinitions(array(
      'type' => 'entity',
      'entity type' => $this->entityType,
      'bundle' => $this->bundle(),
    ));
  }

  public function access($account) {
    // TODO: Implement access() method.
  }

  public function validate() {
    // TODO: Implement validate() method.
  }
}
