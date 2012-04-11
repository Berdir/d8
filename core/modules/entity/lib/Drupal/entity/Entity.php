<?php

/**
 * @file
 * Definition of Drupal\entity\Entity.
 */

namespace Drupal\entity;

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
   * Information about the entity's type.
   *
   * @var array
   */
  protected $entityInfo;

  /**
   * The entity ID key.
   *
   * @var string
   */
  protected $idKey;

  /**
   * The entity bundle key.
   *
   * @var string
   */
  protected $bundleKey;

  /**
   * Boolean indicating whether the entity should be forced to be new.
   *
   * @var bool
   */
  protected $enforceIsNew;

  /**
   * Constructs a new entity object.
   */
  public function __construct(array $values = array(), $entity_type) {
    $this->entityType = $entity_type;
    $this->setUp();
    // Set initial values.
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * Sets up the object instance on construction or unserialization.
   */
  protected function setUp() {
    $this->entityInfo = entity_get_info($this->entityType);
    $this->idKey = $this->entityInfo['entity keys']['id'];
    $this->bundleKey = !empty($this->entityInfo['entity keys']['bundle']) ? $this->entityInfo['entity keys']['bundle'] : NULL;
  }

  /**
   * Implements EntityInterface::id().
   */
  public function id() {
    return isset($this->{$this->idKey}) ? $this->{$this->idKey} : NULL;
  }

  /**
   * Implements EntityInterface::isNew().
   */
  public function isNew() {
    return !empty($this->enforceIsNew) || empty($this->{$this->idKey});
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
    return isset($this->bundleKey) ? $this->{$this->bundleKey} : $this->entityType;
  }

  /**
   * Implements EntityInterface::label().
   *
   * @see entity_label()
   */
  public function label() {
    $label = FALSE;
    if (isset($this->entityInfo['label callback']) && function_exists($this->entityInfo['label callback'])) {
      $label = $this->entityInfo['label callback']($this->entityType, $this);
    }
    elseif (!empty($this->entityInfo['entity keys']['label']) && isset($this->{$this->entityInfo['entity keys']['label']})) {
      $label = $this->{$this->entityInfo['entity keys']['label']};
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
    if (isset($this->entityInfo['bundles'][$bundle]['uri callback'])) {
      $uri_callback = $this->entityInfo['bundles'][$bundle]['uri callback'];
    }
    elseif (isset($this->entityInfo['uri callback'])) {
      $uri_callback = $this->entityInfo['uri callback'];
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
    if ($this->entityInfo['fieldable'] && ($default_language = $this->language())) {
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
    // Handle fields.
    if ($this->entityInfo['fieldable'] && field_info_instance($this->entityType, $property_name, $this->bundle())) {
      $field = field_info_field($property_name);
      $langcode = $this->getFieldLangcode($field, $langcode);
      return isset($this->{$property_name}[$langcode]) ? $this->{$property_name}[$langcode] : NULL;
    }
    else {
      // Handle properties being not fields.
      // @todo: Add support for translatable properties being not fields.
      return isset($this->{$property_name}) ? $this->{$property_name} : NULL;
    }
  }

  /**
   * Implements EntityInterface::set().
   */
  public function set($property_name, $value, $langcode = NULL) {
    // Handle fields.
    if ($this->entityInfo['fieldable'] && field_info_instance($this->entityType, $property_name, $this->bundle())) {
      $field = field_info_field($property_name);
      $langcode = $this->getFieldLangcode($field, $langcode);
      $this->{$property_name}[$langcode] = $value;
    }
    else {
      // Handle properties being not fields.
      // @todo: Add support for translatable properties being not fields.
      $this->{$property_name} = $value;
    }
  }

  /**
   * Determines the language code to use for accessing a field value in a certain language.
   */
  protected function getFieldLangcode($field, $langcode = NULL) {
    // Only apply the given langcode if the entity is language-specific.
    // Otherwise translatable fields are handled as non-translatable fields.
    if (field_is_translatable($this->entityType, $field) && ($default_language = $this->language())) {
      // For translatable fields the values in default language are stored using
      // the language code of the default language.
      return isset($langcode) ? $langcode : $default_language->langcode;
    }
    else {
      // Non-translatable fields always use LANGUAGE_NOT_SPECIFIED.
      return LANGUAGE_NOT_SPECIFIED;
    }
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
    $duplicate->{$this->idKey} = NULL;
    return $duplicate;
  }

  /**
   * Implements EntityInterface::entityInfo().
   */
  public function entityInfo() {
    return $this->entityInfo;
  }

  /**
   * Serializes only what is necessary.
   *
   * See @link http://www.php.net/manual/language.oop5.magic.php#language.oop5.magic.sleep PHP Magic Methods @endlink.
   */
  public function __sleep() {
    $vars = get_object_vars($this);
    unset($vars['entityInfo'], $vars['idKey'], $vars['bundleKey']);
    // Also key the returned array with the variable names so the method may
    // be easily overridden and customized.
    return drupal_map_assoc(array_keys($vars));
  }

  /**
   * Invokes setUp() on unserialization.
   *
   * See @link http://www.php.net/manual/language.oop5.magic.php#language.oop5.magic.sleep PHP Magic Methods @endlink
   */
  public function __wakeup() {
    $this->setUp();
  }
}
