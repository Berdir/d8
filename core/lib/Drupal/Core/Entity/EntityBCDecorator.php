<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityBCDecorator.
 */

namespace Drupal\Core\Entity;

use IteratorAggregate;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\ContextAwareInterface;

/**
 * Provides backwards compatible (BC) access to entity fields.
 *
 * Allows using entities converted to the new Entity Field API with the previous
 * way of accessing fields or properties. For example, via the backwards
 * compatible (BC) decorator you can do:
 * @code
 *   $node->title = $value;
 *   $node->body[LANGUAGE_NONE][0]['value'] = $value;
 * @endcode
 * Without the BC decorator the same assignment would have to look like this:
 * @code
 *   $node->title->value = $value;
 *   $node->body->value = $value;
 * @endcode
 * Without the BC decorator the language always default to the entity language,
 * whereas a specific translation can be access via the getTranslation() method.
 *
 * The BC decorator should be only used during conversion to the new entity
 * field API, such that existing code can be converted iteratively. Any new code
 * should directly use the new entity field API and avoid using the
 * EntityBCDecorator, if possible.
 *
 * @todo: Remove once everything is converted to use the new entity field API.
 */
class EntityBCDecorator implements IteratorAggregate, EntityInterface {

  /**
   * The EntityInterface object being decorated.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $decorated;

  /**
   * Local cache for field definitions.
   *
   * @var array
   */
  protected $definitions;

  /**
   * Constructs a Drupal\Core\Entity\EntityCompatibilityDecorator object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $decorated
   *   The decorated entity.
   * @param array
   *   An array of field definitions.
   */
  function __construct(EntityNG $decorated, array &$definitions) {
    $this->decorated = $decorated;
    $this->definitions = &$definitions;
  }

  /**
   * Overrides Entity::getOriginalEntity().
   */
  public function getOriginalEntity() {
    return $this->decorated;
  }

  /**
   * Overrides Entity::getBCEntity().
   */
  public function getBCEntity() {
    return $this;
  }

  /**
   * Implements the magic method for getting object properties.
   *
   * Directly accesses the plain field values, as done in Drupal 7.
   */
  public function &__get($name) {
    // Directly return the original property.
    if ($name == 'original') {
      return $this->decorated->values[$name];
    }

    // We access the protected 'values' and 'fields' properties of the decorated
    // entity via the magic getter - which returns them by reference for us. We
    // do so, as providing references to these arrays would make $entity->values
    // and $entity->fields reference themselves, which is problematic during
    // __clone() (this is something we cannot work-a-round easily as an unset()
    // on the variable is problematic in conjunction with the magic
    // getter/setter).

    if (!empty($this->decorated->fields[$name])) {
      // Any field value set via the new Entity Field API will be stored inside
      // the field objects managed by the entity, thus we need to ensure
      // $this->decorated->values reflects the latest values first.
      foreach ($this->decorated->fields[$name] as $langcode => $field) {
        // Only set if it's not empty, otherwise there can be ghost values.
        if (!$field->isEmpty()) {
          $this->decorated->values[$name][$langcode] = $field->getValue();
        }
      }
      // The returned values might be changed by reference, so we need to remove
      // the field object to avoid the field object and the value getting out of
      // sync. That way, the next field object instantiated by EntityNG will
      // receive the possibly updated value.
      unset($this->decorated->fields[$name]);
    }
    // When accessing values for entity properties that have been converted to
    // an entity field, provide direct access to the plain value. This makes it
    // possible to use the BC-decorator with properties; e.g., $node->title.
    if (isset($this->definitions[$name]) && empty($this->definitions[$name]['configurable'])) {
      if (!isset($this->decorated->values[$name][LANGUAGE_DEFAULT])) {
        $this->decorated->values[$name][LANGUAGE_DEFAULT][0]['value'] = NULL;
      }
      $value = $this->decorated->values[$name][LANGUAGE_DEFAULT];
      if (is_array($value)) {
        $value = !empty($value[0]) ? reset($value[0]) : NULL;
      }
      return $value;
    }
    else {
      // Allow accessing field values in entity default language other than
      // LANGUAGE_DEFAULT by mapping the values to LANGUAGE_DEFAULT. This is
      // necessary as EntityNG does key values in default language always with
      // LANGUAGE_DEFAULT while field API expects them to be keyed by langcode.
      $langcode = $this->decorated->language()->langcode;
      if ($langcode != LANGUAGE_DEFAULT && isset($this->decorated->values[$name]) && is_array($this->decorated->values[$name])) {
        if (isset($this->decorated->values[$name][LANGUAGE_DEFAULT]) && !isset($this->decorated->values[$name][$langcode])) {
          $this->decorated->values[$name][$langcode] = &$this->decorated->values[$name][LANGUAGE_DEFAULT];
        }
      }
      if (!isset($this->decorated->values[$name])) {
        $this->decorated->values[$name] = NULL;
      }
      return $this->decorated->values[$name];
    }
  }

  /**
   * Implements the magic method for setting object properties.
   *
   * Directly writes to the plain field values, as done by Drupal 7.
   */
  public function __set($name, $value) {

    // The property revision was used to create a new revision - do so as well.
    // @TODO Did this apply only to node or for other entity types too?
    if ($name == 'revision') {
      $this->setNewRevision($value);
    }

    $defined = isset($this->definitions[$name]);
    // When updating values for entity properties that have been converted to
    // an entity field, directly write to the plain value. This makes it
    // possible to use the BC-decorator with properties; e.g., $node->title.
    if ($defined && empty($this->definitions[$name]['configurable'])) {
      $this->decorated->values[$name][LANGUAGE_DEFAULT] = $value;
    }
    else {
      if ($defined && is_array($value)) {
        // If field API sets a value with a langcode in entity language, move it
        // to LANGUAGE_DEFAULT.
        // This is necessary as EntityNG does key values in default language always
        // with LANGUAGE_DEFAULT while field API expects them to be keyed by
        // langcode.
        foreach ($value as $langcode => $data) {
          if ($langcode != LANGUAGE_DEFAULT && $langcode == $this->decorated->language()->langcode) {
            $value[LANGUAGE_DEFAULT] = $data;
            unset($value[$langcode]);
          }
        }
      }
      $this->decorated->values[$name] = $value;
    }
    // Remove the field object to avoid the field object and the value getting
    // out of sync. That way, the next field object instantiated by EntityNG
    // will hold the updated value.
    unset($this->decorated->fields[$name]);
  }

  /**
   * Implements the magic method for isset().
   */
  public function __isset($name) {
    $value = $this->__get($name);
    // Horrible code but necessary because non existing properties are
    // initialized with the value NULL in a proper data-structure.
    if (($isset = isset($value)) && is_array($value) && !empty($value)) {
      foreach ($value as $langcode => $data) {
        if (is_array($data)) {
          foreach ($data as $delta => $value) {
            if (!is_null($value)) {
              return TRUE;
            }
          }
          // An empty array is considered as set since that's unlikely an
          // artificially initialized one.
          return empty($data);
        }
        else {
          // Temporarly commented out als this results in an incorrect FALSE for
          // an array where the first key is empty ($node->path when alias is
          // being deleted). Is this really necessary?
          //return !empty($data);
        }
      }
    }
    return $isset;
  }

  /**
   * Implements the magic method for unset().
   */
  public function __unset($name) {
    $value = &$this->__get($name);
    // Unset should act like the property isn't set and thus ignored. Following
    // structure will provide that behaviour with EntityBCDecorator::__isset().
    $value = array(LANGUAGE_NOT_SPECIFIED => NULL);
  }

  /**
   * Implements the magic method for clone().
   */
  function __clone() {
    $this->decorated = clone $this->decorated;
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function access($operation = 'view', \Drupal\user\Plugin\Core\Entity\User $account = NULL) {
    return $this->decorated->access($operation, $account);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function get($property_name) {
    return $this->decorated->get($property_name);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function set($property_name, $value) {
    return $this->decorated->set($property_name, $value);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getProperties($include_computed = FALSE) {
    return $this->decorated->getProperties($include_computed);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getPropertyValues() {
    return $this->decorated->getPropertyValues();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function setPropertyValues($values) {
    return $this->decorated->setPropertyValues($values);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getPropertyDefinition($name) {
    return $this->decorated->getPropertyDefinition($name);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getPropertyDefinitions() {
    return $this->decorated->getPropertyDefinitions();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function isEmpty() {
    return $this->decorated->isEmpty();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getIterator() {
    return $this->decorated->getIterator();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function id() {
    return $this->decorated->id();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function uuid() {
    return $this->decorated->uuid();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function isNew() {
    return $this->decorated->isNew();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function isNewRevision() {
    return $this->decorated->isNewRevision();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function setNewRevision($value = TRUE) {
    return $this->decorated->setNewRevision($value);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function enforceIsNew($value = TRUE) {
    return $this->decorated->enforceIsNew($value);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function entityType() {
    return $this->decorated->entityType();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function bundle() {
    return $this->decorated->bundle();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function label($langcode = NULL) {
    return $this->decorated->label($langcode);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function uri() {
    return $this->decorated->uri();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function save() {
    return $this->decorated->save();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function delete() {
    return $this->decorated->delete();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function createDuplicate() {
    return $this->decorated->createDuplicate();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function entityInfo() {
    return $this->decorated->entityInfo();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getRevisionId() {
    return $this->decorated->getRevisionId();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function isDefaultRevision($new_value = NULL) {
    return $this->decorated->isDefaultRevision($new_value);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function language() {
    return $this->decorated->language();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getTranslationLanguages($include_default = TRUE) {
    return $this->decorated->getTranslationLanguages($include_default);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getTranslation($langcode, $strict = TRUE) {
    return $this->decorated->getTranslation($langcode, $strict);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getName() {
    return $this->decorated->getName();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getRoot() {
    return $this->decorated->getRoot();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getPropertyPath() {
    return $this->decorated->getPropertyPath();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getParent() {
    return $this->decorated->getParent();
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function setContext($name = NULL, ContextAwareInterface $parent = NULL) {
    $this->decorated->setContext($name, $parent);
  }

  /**
   * Forwards the call to the decorated entity.
   */
  public function getExportProperties() {
    $this->decorated->getExportProperties();
  }
}
