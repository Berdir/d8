<?php

/**
 * @file
 * Definition of Drupal\entity\StorableBase.
 */

namespace Drupal\entity;

use Drupal\Component\Uuid\Uuid;

/**
 * Defines a base entity class.
 *
 * Default implementation of StorableInterface.
 *
 * This class can be used as-is by simple entity types. Entity types requiring
 * special handling can extend the class.
 */
abstract class StorableBase implements StorableInterface {

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
   * Indicates whether this is the current revision.
   *
   * @var bool
   */
  public $isCurrentRevision = TRUE;

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
   * Implements StorableInterface::id().
   */
  public function id() {
    return isset($this->id) ? $this->id : NULL;
  }

  /**
   * Implements StorableInterface::uuid().
   */
  public function uuid() {
    return isset($this->uuid) ? $this->uuid : NULL;
  }

  /**
   * Implements StorableInterface::isNew().
   */
  public function isNew() {
    return !empty($this->enforceIsNew) || !$this->id();
  }

  /**
   * Implements StorableInterface::enforceIsNew().
   */
  public function enforceIsNew($value = TRUE) {
    $this->enforceIsNew = $value;
  }

  /**
   * Implements StorableInterface::entityType().
   */
  public function entityType() {
    return $this->entityType;
  }

  /**
   * Implements StorableInterface::bundle().
   */
  public function bundle() {
    return $this->entityType;
  }

  /**
   * Implements StorableInterface::label().
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
   * Implements StorableInterface::uri().
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
   * Implements StorableInterface::get().
   */
  public function get($property_name, $langcode = NULL) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
    return isset($this->{$property_name}) ? $this->{$property_name} : NULL;
  }

  /**
   * Implements DataStructureInterface::set().
   */
  public function set($property_name, $value) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
    $this->{$property_name} = $value;
  }

  /**
   * Implements DataStructureTranslatableInterface::language().
   */
  public function language() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
    return module_exists('locale') ? language_load($this->langcode) : FALSE;
  }

  /**
   * Implements DataStructureTranslatableInterface::getTranslation().
   */
  public function getTranslation($langcode) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements DataStructureTranslatableInterface::getTranslationLanguages().
   */
  public function getTranslationLanguages($include_default = TRUE) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements StorableInterface::save().
   */
  public function save() {
    return entity_get_controller($this->entityType)->save($this);
  }

  /**
   * Implements StorableInterface::delete().
   */
  public function delete() {
    if (!$this->isNew()) {
      entity_get_controller($this->entityType)->delete(array($this->id()));
    }
  }

  /**
   * Implements StorableInterface::createDuplicate().
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
   * Implements StorableInterface::entityInfo().
   */
  public function entityInfo() {
    return entity_get_info($this->entityType);
  }

  /**
   * Implements Drupal\entity\StorableInterface::getRevisionId().
   */
  public function getRevisionId() {
    return NULL;
  }

  /**
   * Implements Drupal\entity\StorableInterface::isCurrentRevision().
   */
  public function isCurrentRevision() {
    return $this->isCurrentRevision;
  }

  /**
   * Implements DataStructureTranslatableInterface::getProperties().
   */
  public function getProperties($include_computed = FALSE) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements DataStructureTranslatableInterface::setProperties().
   */
  public function setProperties($properties) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements DataStructureTranslatableInterface::getPropertyDefinition().
   */
  public function getPropertyDefinition($name) {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements DataStructureTranslatableInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements DataStructureTranslatableInterface::toArray().
   */
  public function toArray() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }

  /**
   * Implements DataStructureTranslatableInterface::getIterator().
   */
  public function getIterator() {
    // @todo: Replace by EntityNG implementation once all entity types have been
    // converted to use the entity property API.
  }
}
