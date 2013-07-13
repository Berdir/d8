<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityStorageControllerBase.
 */

namespace Drupal\Core\Entity;
use Drupal\field\FieldInterface;
use Drupal\field\FieldInstanceInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * A base entity storage controller class.
 */
abstract class EntityStorageControllerBase implements EntityStorageControllerInterface, EntityControllerInterface {

  /**
   * Static cache of entities.
   *
   * @var array
   */
  protected $entityCache = array();

  /**
   * Whether this entity type should use the static cache.
   *
   * Set by entity info.
   *
   * @var boolean
   */
  protected $cache;

  /**
   * Entity type for this controller instance.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Array of information about the entity.
   *
   * @var array
   *
   * @see entity_get_info()
   */
  protected $entityInfo;

  /**
   * Additional arguments to pass to hook_TYPE_load().
   *
   * Set before calling Drupal\Core\Entity\DatabaseStorageController::attachLoad().
   *
   * @var array
   */
  protected $hookLoadArguments = array();

  /**
   * Name of the entity's ID field in the entity database table.
   *
   * @var string
   */
  protected $idKey;

  /**
   * Name of entity's UUID database table field, if it supports UUIDs.
   *
   * Has the value FALSE if this entity does not use UUIDs.
   *
   * @var string
   */
  protected $uuidKey;

  /**
   * Constructs an EntityStorageControllerBase instance.
   *
   * @param string $entity_type
   *   The entity type for which the instance is created.
   * @param array $entity_info
   *   An array of entity info for the entity type.
   */
  public function __construct($entity_type, $entity_info) {
    $this->entityType = $entity_type;
    $this->entityInfo = $entity_info;
    // Check if the entity type supports static caching of loaded entities.
    $this->cache = !empty($this->entityInfo['static_cache']);
  }

  /**
   * {@inheritdoc}
   */
  public function loadUnchanged($id) {
    $this->resetCache(array($id));
    return $this->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $ids = NULL) {
    if ($this->cache && isset($ids)) {
      foreach ($ids as $id) {
        unset($this->entityCache[$id]);
      }
    }
    else {
      $this->entityCache = array();
    }
  }

  /**
   * Gets entities from the static cache.
   *
   * @param $ids
   *   If not empty, return entities that match these IDs.
   *
   * @return
   *   Array of entities from the entity cache.
   */
  protected function cacheGet($ids) {
    $entities = array();
    // Load any available entities from the internal cache.
    if ($this->cache && !empty($this->entityCache)) {
      $entities += array_intersect_key($this->entityCache, array_flip($ids));
    }
    return $entities;
  }

  /**
   * Stores entities in the static entity cache.
   *
   * @param $entities
   *   Entities to store in the cache.
   */
  protected function cacheSet($entities) {
    if ($this->cache) {
      $this->entityCache += $entities;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invokeFieldMethod($method, EntityInterface $entity) {
    foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
      // @todo getTranslation() only works on NG entities. Remove the condition
      // and the second code branch when all core entity types are converted.
      if ($translation = $entity->getTranslation($langcode)) {
        foreach ($translation as $field_name => $field) {
          $field->$method();
        }
      }
      else {
        // For BC entities, iterate through fields and instantiate NG items
        // objects manually.
        $definitions = \Drupal::entityManager()->getFieldDefinitions($entity->entityType(), $entity->bundle());
        foreach ($definitions as $field_name => $definition) {
          if (!empty($definition['configurable'])) {
            // Create the items object.
            $itemsBC = isset($entity->{$field_name}[$langcode]) ? $entity->{$field_name}[$langcode] : array();
            // @todo Exception : this calls setValue(), tries to set the
            // 'formatted' property. For now, this is worked around by
            // commenting out the Exception in TextProcessed::setValue().
            $items = \Drupal::typedData()->create($definition, $itemsBC, $field_name, $entity);
            $items->$method();

            // Put back the items values in the entity.
            $itemsBC = $items->getValue(TRUE);
            if ($itemsBC !== array() || isset($entity->{$field_name}[$langcode])) {
              $entity->{$field_name}[$langcode] = $itemsBC;
            }
          }
        }
      }
    }
  }

   /**
   * {@inheritdoc}
   */
  public function invokeFieldItemPrepareCache(EntityInterface $entity) {
    foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
      // @todo getTranslation() only works on NG entities. Remove the condition
      // and the second code branch when all core entity types are converted.
      if ($translation = $entity->getTranslation($langcode)) {
        foreach ($translation->getPropertyDefinitions() as $property => $definition) {
          $type_definition = \Drupal::typedData()->getDefinition($definition['type']);
          // Only create the item objects if needed.
          if (is_subclass_of($type_definition['class'], '\Drupal\Core\Entity\Field\PrepareCacheInterface')
            // Prevent legacy field types from skewing performance too much by
            // checking the existence of the legacy function directly, instead
            // of making LegacyConfigFieldItem implement PrepareCacheInterface.
            // @todo Remove once all core field types have been converted (see
            // http://drupal.org/node/2014671).
            || (is_subclass_of($type_definition['class'], '\Drupal\field\Plugin\field\field_type\LegacyConfigFieldItem') && function_exists($type_definition['module'] . '_field_load'))) {

            // Call the prepareCache() method directly on each item
            // individually.
            foreach ($translation->get($property) as $item) {
              $item->prepareCache();
            }
          }
        }
      }
      else {
        // For BC entities, iterate through the fields and instantiate NG items
        // objects manually.
        $definitions = \Drupal::entityManager()->getFieldDefinitions($entity->entityType(), $entity->bundle());
        foreach ($definitions as $field_name => $definition) {
          if (!empty($definition['configurable'])) {
            $type_definition = \Drupal::typedData()->getDefinition($definition['type']);
            // Only create the item objects if needed.
            if (is_subclass_of($type_definition['class'], '\Drupal\Core\Entity\Field\PrepareCacheInterface')
              // @todo Remove once all core field types have been converted
              // (see http://drupal.org/node/2014671).
              || (is_subclass_of($type_definition['class'], '\Drupal\field\Plugin\field\field_type\LegacyConfigFieldItem') && function_exists($type_definition['module'] . '_field_load'))) {

              // Create the items object.
              $items = isset($entity->{$field_name}[$langcode]) ? $entity->{$field_name}[$langcode] : array();
              $itemsNG = \Drupal::typedData()->create($definition, $items, $field_name, $entity);

              foreach ($itemsNG as $item) {
                $item->prepareCache();
              }

              // Put back the items values in the entity.
              $items = $itemsNG->getValue(TRUE);
              if ($items !== array() || isset($entity->{$field_name}[$langcode])) {
                $entity->{$field_name}[$langcode] = $items;
              }
            }
          }
        }
      }
    }
  }

  /**
   * Invokes a hook on behalf of the entity.
   *
   * @param $hook
   *   One of 'presave', 'insert', 'update', 'predelete', 'delete', or
   *  'revision_delete'.
   * @param \Drupal\Core\Entity\EntityInterface  $entity
   *   The entity object.
   */
  protected function invokeHook($hook, EntityInterface $entity) {
    $method = Container::camelize('field_' . $hook);
    if (!empty($this->entityInfo['fieldable']) && method_exists($this, $method)) {
      $this->$method($entity);
    }
    // Invoke the hook.
    module_invoke_all($this->entityType . '_' . $hook, $entity);
    // Invoke the respective entity-level hook.
    module_invoke_all('entity_' . $hook, $entity, $this->entityType);
  }

  /**
   * Checks translation statuses and invoke the related hooks if needed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being saved.
   */
  protected function invokeTranslationHooks(EntityInterface $entity) {
    $translations = $entity->getTranslationLanguages(FALSE);
    $original_translations = $entity->original->getTranslationLanguages(FALSE);
    $all_translations = array_keys($translations + $original_translations);

    // Notify modules of translation insertion/deletion.
    foreach ($all_translations as $langcode) {
      if (isset($translations[$langcode]) && !isset($original_translations[$langcode])) {
        $this->invokeHook('translation_insert', $entity->getTranslation($langcode));
      }
      elseif (!isset($translations[$langcode]) && isset($original_translations[$langcode])) {
        $this->invokeHook('translation_delete', $entity->getTranslation($langcode));
      }
    }
  }

  /**
   * Loads fields for the current revisions of a group of entities.
   *
   * Loads all fields for each entity object in a group of a single entity type.
   * The loaded field values are added directly to the entity objects.
   *
   * @param $entities
   *   An array of entities for which to load fields, keyed by entity ID.
   * @param $age
   *   FIELD_LOAD_CURRENT to load the most recent revision for all fields, or
   *   FIELD_LOAD_REVISION to load the version indicated by each entity.
   */
  protected function fieldLoad($entities, $age) {
    if (empty($entities)) {
      return;
    }

    // Only the most current revision of non-deleted fields for cacheable entity
    // types can be cached.
    $load_current = $age == FIELD_LOAD_CURRENT;
    $info = entity_get_info($this->entityType);
    $use_cache = $load_current && $info['field_cache'];

    // Ensure we are working with a BC mode entity.
    foreach ($entities as $id => $entity) {
      $entities[$id] = $entity->getBCEntity();
    }

    // Assume all entities will need to be queried. Entities found in the cache
    // will be removed from the list.
    $queried_entities = $entities;

    // Fetch available entities from cache, if applicable.
    if ($use_cache) {
      // Build the list of cache entries to retrieve.
      $cids = array();
      foreach ($entities as $id => $entity) {
        $cids[] = "field:$this->entityType:$id";
      }
      $cache = cache('field')->getMultiple($cids);
      // Put the cached field values back into the entities and remove them from
      // the list of entities to query.
      foreach ($entities as $id => $entity) {
        $cid = "field:$this->entityType:$id";
        if (isset($cache[$cid])) {
          unset($queried_entities[$id]);
          foreach ($cache[$cid]->data as $field_name => $values) {
            $entity->$field_name = $values;
          }
        }
      }
    }

    // Fetch other entities from their storage location.
    if ($queried_entities) {
      // The invoke order is:
      // - hook_field_storage_pre_load()
      // - Entity storage controller's doFieldLoad() method
      // - Field class's prepareCache() method.
      // - hook_field_attach_load()

      // Invoke hook_field_storage_pre_load(): let any module load field data
      // before the storage engine, accumulating along the way.
      foreach (module_implements('field_storage_pre_load') as $module) {
        $function = $module . '_field_storage_pre_load';
        $function($this->entityType, $queried_entities, $age);
      }

      // Let the storage controller actually load the values.
      $this->doFieldLoad($queried_entities, $age);

      // Invoke the field type's prepareCache() method.
      foreach ($queried_entities as $entity) {
        \Drupal::entityManager()
          ->getStorageController($this->entityType)
          ->invokeFieldItemPrepareCache($entity);
      }

      // Invoke hook_field_attach_load(): let other modules act on loading the
      // entity.
      module_invoke_all('field_attach_load', $this->entityType, $queried_entities, $age);

      // Build cache data.
      if ($use_cache) {
        foreach ($queried_entities as $id => $entity) {
          $data = array();
          $instances = field_info_instances($this->entityType, $entity->bundle());
          foreach ($instances as $instance) {
            $data[$instance['field_name']] = $queried_entities[$id]->{$instance['field_name']};
          }
          $cid = "field:$this->entityType:$id";
          cache('field')->set($cid, $data);
        }
      }
    }
  }

  /**
   * Save field data for a new entity.
   *
   * The passed-in entity must already contain its id and (if applicable)
   * revision id attributes.
   *
   * It should be enough to override doFieldInsert() instead of this method.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity with fields to save.
   * @return
   *   Default values (if any) will be added to the $entity parameter for fields
   *   it leaves unspecified.
   */
  protected function fieldInsert(EntityInterface $entity) {
    // Ensure we are working with a BC mode entity.
    $entity = $entity->getBCEntity();

    // Let any module insert field data before the storage engine.
    foreach (module_implements('field_storage_pre_insert') as $module) {
      $function = $module . '_field_storage_pre_insert';
      $function($entity);
    }
    $this->doFieldInsert($entity);
  }

  /**
   * Saves field data for an existing entity.
   *
   * It should be enough to override doFieldUpdate() instead of this method.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity with fields to save.
   */
  protected function fieldUpdate(EntityInterface $entity) {
    // Ensure we are working with a BC mode entity.
    $entity = $entity->getBCEntity();

    // Let any module update field data before the storage engine
    foreach (module_implements('field_storage_pre_update') as $module) {
      $function = $module . '_field_storage_pre_update';
      $function($entity);
    }

    $this->doFieldUpdate($entity);

    $entity_info = $entity->entityInfo();
    if ($entity_info['field_cache']) {
      cache('field')->delete('field:' . $entity->entityType() . ':' . $entity->id());
    }
  }

  /**
   * Deletes field data for an existing entity. This deletes all revisions of
   * field data for the entity.
   *
   * It should be enough to override doFieldDelete() instead of this method.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose field data to delete.
   */
  protected function fieldDelete(EntityInterface $entity) {
    // Ensure we are working with a BC mode entity.
    $entity = $entity->getBCEntity();

    $this->doFieldDelete($entity);

    $entity_info = $entity->entityInfo();
    if ($entity_info['field_cache']) {
      cache('field')->delete('field:' . $entity->entityType() . ':' . $entity->id());
    }
  }

  /**
   * Delete field data for a single revision of an existing entity. The passed
   * entity must have a revision ID attribute.
   *
   * It should be enough to override doFieldRevisionDelete() instead of this
   * method.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity with fields to save.
   */
  protected function fieldRevisionDelete(EntityInterface $entity) {
    $this->dofieldRevisionDelete($entity->getBCEntity());
  }

  /**
   * Load configurable fields from storage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  protected function doFieldLoad($queried_entities, $age) { }

  /**
   * Insert configurable fields into storage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  protected function doFieldInsert(EntityInterface $entity) { }

  /**
   * Update configurable fields in storage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  protected function doFieldUpdate(EntityInterface $entity) { }

  /**
   * Delete configurable fields from storage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  protected function doFieldDelete(EntityInterface $entity) { }

  /**
   * Delete specific revision of configurable fields from storage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  protected function doFieldRevisionDelete(EntityInterface $entity) { }

  /**
   * {@inheritdoc}
   */
  public function handleFieldUpdate(FieldInterface $field, FieldInterface $original) { }

  /**
   * {@inheritdoc}
   */
  public function handleFieldDelete(FieldInterface $field) { }

  /**
   * {@inheritdoc}
   */
  public function handleInstanceCreate(FieldInstanceInterface $instance, $first) { }

  /**
   * {@inheritdoc}
   */
  public function handleInstanceDelete(FieldInstanceInterface $instance) { }

  /**
   * {@inheritdoc}
   */
  public function handleBundleCreate($bundle) { }

  /**
   * {@inheritdoc}
   */
  public function handleBundleRename($bundle, $bundle_new) { }

  /**
   * {@inheritdoc}
   */
  public function handleBundleDelete($bundle) { }

  /**
   * {@inheritdoc}
   */
  public function fieldPurgeData(EntityInterface $entity, FieldInstanceInterface $instance) {
    $values = $this->fieldValues($entity, $instance);
    $field = $instance->getField();
    foreach ($values as $value) {
      $definition = _field_generate_entity_field_definition($field, $instance);
      $items = \Drupal::typedData()->create($definition, $value, $field->id(), $entity);
      $items->delete();
    }
  }

  /**
   * Gets the field values for a single field of a single entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\field\Plugin\Core\Entity\FieldInstanceInterface $instance
   *   The field instance.
   *
   * @return array
   *   The field values.
   */
  protected function fieldValues(EntityInterface $entity, FieldInstanceInterface $instance) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldPurge(FieldInterface $field) { }

}
