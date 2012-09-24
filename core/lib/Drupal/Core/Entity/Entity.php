<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\Entity.
 */

namespace Drupal\Core\Entity;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Language\Language;

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
   * Indicates whether this is the default revision.
   *
   * @var bool
   */
  protected $isDefaultRevision = TRUE;

  /**
   * Constructs a new entity object.
   */
  public function __construct(array $values = array(), $entity_type) {
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
   * Implements EntityInterface::uuid().
   */
  public function uuid() {
    return isset($this->uuid) ? $this->uuid : NULL;
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
    $label = NULL;
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
   * Implements EntityInterface::get().
   */
  public function get($property_name, $langcode = NULL) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
    return isset($this->{$property_name}) ? $this->{$property_name} : NULL;
  }

  /**
   * Implements ComplexDataInterface::set().
   */
  public function set($property_name, $value) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
    $this->{$property_name} = $value;
  }

  /**
   * Implements TranslatableComplexDataInterface::getProperties().
   */
  public function getProperties($include_computed = FALSE) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements TranslatableComplexDataInterface::setProperties().
   */
  public function setProperties($properties) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements TranslatableComplexDataInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements TranslatableComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements TranslatableComplexDataInterface::toArray().
   */
  public function toArray() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements TranslatableComplexDataInterface::isEmpty().
   */
  public function isEmpty() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements TranslatableComplexDataInterface::getIterator().
   */
  public function getIterator() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements TranslatableComplexDataInterface::language().
   */
  public function language() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
    return !empty($this->langcode) ? language_load($this->langcode) : new Language(array('langcode' => LANGUAGE_NOT_SPECIFIED));
  }

  /**
   * Implements TranslatableComplexDataInterface::getTranslation().
   */
  public function getTranslation($langcode) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Returns the languages the entity is translated to.
   *
   * @todo: Remove once all entity types implement the entity property API. This
   * is deprecated by
   * TranslatableComplexDataInterface::getTranslationLanguages().
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
   * Implements TranslatableComplexDataInterface::getTranslationLanguages().
   */
  public function getTranslationLanguages($include_default = TRUE) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
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
    $entity_info = $this->entityInfo();
    $this->{$entity_info['entity keys']['id']} = NULL;

    // Check if the entity type supports UUIDs and generate a new one if so.
    if (!empty($entity_info['entity keys']['uuid'])) {
      $uuid = new Uuid();
      $duplicate->{$entity_info['entity keys']['uuid']} = $uuid->generate();
    }
    return $duplicate;
  }

  /**
   * Implements EntityInterface::entityInfo().
   */
  public function entityInfo() {
    return entity_get_info($this->entityType);
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::getRevisionId().
   */
  public function getRevisionId() {
    return NULL;
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::isDefaultRevision().
   */
  public function isDefaultRevision($new_value = NULL) {
    $return = $this->isDefaultRevision;
    if (isset($new_value)) {
      $this->isDefaultRevision = (bool) $new_value;
    }
    return $return;
  }
}
