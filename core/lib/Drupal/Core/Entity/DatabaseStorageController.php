<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\DatabaseStorageController.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Language\Language;
use Drupal\field\FieldInfo;
use Drupal\field\Plugin\Core\Entity\Field;
use PDO;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Component\Uuid\Uuid;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base entity controller class.
 *
 * Default implementation of Drupal\Core\Entity\EntityStorageControllerInterface.
 *
 * This class can be used as-is by most simple entity types. Entity types
 * requiring special handling can extend the class.
 */
class DatabaseStorageController extends EntityStorageControllerBase {

  /**
   * Name of entity's revision database table field, if it supports revisions.
   *
   * Has the value FALSE if this entity does not use revisions.
   *
   * @var string
   */
  protected $revisionKey;

  /**
   * The table that stores revisions, if the entity supports revisions.
   *
   * @var string
   */
  protected $revisionTable;

  /**
   * Whether this entity type should use the static cache.
   *
   * Set by entity info.
   *
   * @var boolean
   */
  protected $cache;

  /**
   * Active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The field info object.
   *
   * @var \Drupal\field\FieldInfo
   */
  protected $fieldInfo;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, $entity_type, array $entity_info) {
    return new static(
      $entity_type,
      $entity_info,
      $container->get('database'),
      $container->get('field.info')
    );
  }

  /**
   * Constructs a DatabaseStorageController object.
   *
   * @param string $entity_type
   *   The entity type for which the instance is created.
   * @param array $entity_info
   *   An array of entity info for the entity type.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   */
  public function __construct($entity_type, array $entity_info, Connection $database, FieldInfo $field_info) {
    parent::__construct($entity_type, $entity_info);

    $this->database = $database;
    $this->fieldInfo = $field_info;

    // Check if the entity type supports IDs.
    if (isset($this->entityInfo['entity_keys']['id'])) {
      $this->idKey = $this->entityInfo['entity_keys']['id'];
    }
    else {
      $this->idKey = FALSE;
    }

    // Check if the entity type supports UUIDs.
    if (!empty($this->entityInfo['entity_keys']['uuid'])) {
      $this->uuidKey = $this->entityInfo['entity_keys']['uuid'];
    }
    else {
      $this->uuidKey = FALSE;
    }

    // Check if the entity type supports revisions.
    if (!empty($this->entityInfo['entity_keys']['revision'])) {
      $this->revisionKey = $this->entityInfo['entity_keys']['revision'];
      $this->revisionTable = $this->entityInfo['revision_table'];
    }
    else {
      $this->revisionKey = FALSE;
    }
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::load().
   */
  public function load(array $ids = NULL) {
    $entities = array();

    // Create a new variable which is either a prepared version of the $ids
    // array for later comparison with the entity cache, or FALSE if no $ids
    // were passed. The $ids array is reduced as items are loaded from cache,
    // and we need to know if it's empty for this reason to avoid querying the
    // database when all requested entities are loaded from cache.
    $passed_ids = !empty($ids) ? array_flip($ids) : FALSE;
    // Try to load entities from the static cache, if the entity type supports
    // static caching.
    if ($this->cache && $ids) {
      $entities += $this->cacheGet($ids);
      // If any entities were loaded, remove them from the ids still to load.
      if ($passed_ids) {
        $ids = array_keys(array_diff_key($passed_ids, $entities));
      }
    }

    // Load any remaining entities from the database. This is the case if $ids
    // is set to NULL (so we load all entities) or if there are any ids left to
    // load.
    if ($ids === NULL || $ids) {
      // Build and execute the query.
      $query_result = $this->buildQuery($ids)->execute();

      if (!empty($this->entityInfo['class'])) {
        // We provide the necessary arguments for PDO to create objects of the
        // specified entity class.
        // @see Drupal\Core\Entity\EntityInterface::__construct()
        $query_result->setFetchMode(PDO::FETCH_CLASS, $this->entityInfo['class'], array(array(), $this->entityType));
      }
      $queried_entities = $query_result->fetchAllAssoc($this->idKey);
    }

    // Pass all entities loaded from the database through $this->attachLoad(),
    // which attaches fields (if supported by the entity type) and calls the
    // entity type specific load callback, for example hook_node_load().
    if (!empty($queried_entities)) {
      $this->attachLoad($queried_entities);
      $entities += $queried_entities;
    }

    if ($this->cache) {
      // Add entities to the cache.
      if (!empty($queried_entities)) {
        $this->cacheSet($queried_entities);
      }
    }

    // Ensure that the returned array is ordered the same as the original
    // $ids array if this was passed in and remove any invalid ids.
    if ($passed_ids) {
      // Remove any invalid ids from the array.
      $passed_ids = array_intersect_key($passed_ids, $entities);
      foreach ($entities as $entity) {
        $passed_ids[$entity->id()] = $entity;
      }
      $entities = $passed_ids;
    }

    return $entities;
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::loadRevision().
   */
  public function loadRevision($revision_id) {
    // Build and execute the query.
    $query_result = $this->buildQuery(array(), $revision_id)->execute();

    if (!empty($this->entityInfo['class'])) {
      // We provide the necessary arguments for PDO to create objects of the
      // specified entity class.
      // @see Drupal\Core\Entity\EntityInterface::__construct()
      $query_result->setFetchMode(PDO::FETCH_CLASS, $this->entityInfo['class'], array(array(), $this->entityType));
    }
    $queried_entities = $query_result->fetchAllAssoc($this->idKey);

    // Pass the loaded entities from the database through $this->attachLoad(),
    // which attaches fields (if supported by the entity type) and calls the
    // entity type specific load callback, for example hook_node_load().
    if (!empty($queried_entities)) {
      $this->attachLoad($queried_entities, $revision_id);
    }
    return reset($queried_entities);
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::deleteRevision().
   */
  public function deleteRevision($revision_id) {
    if ($revision = $this->loadRevision($revision_id)) {
      // Prevent deletion if this is the default revision.
      if ($revision->isDefaultRevision()) {
        throw new EntityStorageException('Default revision can not be deleted');
      }

      $this->database->delete($this->revisionTable)
        ->condition($this->revisionKey, $revision->getRevisionId())
        ->execute();
      $this->invokeFieldMethod('deleteRevision', $revision);
      $this->invokeHook('revision_delete', $revision);
    }
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::loadByProperties().
   */
  public function loadByProperties(array $values = array()) {
    // Build a query to fetch the entity IDs.
    $entity_query = \Drupal::entityQuery($this->entityType);
    $this->buildPropertyQuery($entity_query, $values);
    $result = $entity_query->execute();
    return $result ? $this->load($result) : array();
  }

  /**
   * Builds an entity query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $entity_query
   *   EntityQuery instance.
   * @param array $values
   *   An associative array of properties of the entity, where the keys are the
   *   property names and the values are the values those properties must have.
   */
  protected function buildPropertyQuery(QueryInterface $entity_query, array $values) {
    foreach ($values as $name => $value) {
      $entity_query->condition($name, $value);
    }
  }

  /**
   * Builds the query to load the entity.
   *
   * This has full revision support. For entities requiring special queries,
   * the class can be extended, and the default query can be constructed by
   * calling parent::buildQuery(). This is usually necessary when the object
   * being loaded needs to be augmented with additional data from another
   * table, such as loading node type into comments or vocabulary machine name
   * into terms, however it can also support $conditions on different tables.
   * See Drupal\comment\CommentStorageController::buildQuery() for an example.
   *
   * @param array|null $ids
   *   An array of entity IDs, or NULL to load all entities.
   * @param $revision_id
   *   The ID of the revision to load, or FALSE if this query is asking for the
   *   most current revision(s).
   *
   * @return SelectQuery
   *   A SelectQuery object for loading the entity.
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = $this->database->select($this->entityInfo['base_table'], 'base');

    $query->addTag($this->entityType . '_load_multiple');

    if ($revision_id) {
      $query->join($this->revisionTable, 'revision', "revision.{$this->idKey} = base.{$this->idKey} AND revision.{$this->revisionKey} = :revisionId", array(':revisionId' => $revision_id));
    }
    elseif ($this->revisionKey) {
      $query->join($this->revisionTable, 'revision', "revision.{$this->revisionKey} = base.{$this->revisionKey}");
    }

    // Add fields from the {entity} table.
    $entity_fields = drupal_schema_fields_sql($this->entityInfo['base_table']);

    if ($this->revisionKey) {
      // Add all fields from the {entity_revision} table.
      $entity_revision_fields = drupal_map_assoc(drupal_schema_fields_sql($this->entityInfo['revision_table']));
      // The id field is provided by entity, so remove it.
      unset($entity_revision_fields[$this->idKey]);

      // Remove all fields from the base table that are also fields by the same
      // name in the revision table.
      $entity_field_keys = array_flip($entity_fields);
      foreach ($entity_revision_fields as $key => $name) {
        if (isset($entity_field_keys[$name])) {
          unset($entity_fields[$entity_field_keys[$name]]);
        }
      }
      $query->fields('revision', $entity_revision_fields);

      // Compare revision id of the base and revision table, if equal then this
      // is the default revision.
      $query->addExpression('base.' . $this->revisionKey . ' = revision.' . $this->revisionKey, 'isDefaultRevision');
    }

    $query->fields('base', $entity_fields);

    if ($ids) {
      $query->condition("base.{$this->idKey}", $ids, 'IN');
    }

    return $query;
  }

  /**
   * Attaches data to entities upon loading.
   *
   * This will attach fields, if the entity is fieldable. It calls
   * hook_entity_load() for modules which need to add data to all entities.
   * It also calls hook_TYPE_load() on the loaded entities. For example
   * hook_node_load() or hook_user_load(). If your hook_TYPE_load()
   * expects special parameters apart from the queried entities, you can set
   * $this->hookLoadArguments prior to calling the method.
   * See Drupal\node\NodeStorageController::attachLoad() for an example.
   *
   * @param $queried_entities
   *   Associative array of query results, keyed on the entity ID.
   * @param $load_revision
   *   (optional) TRUE if the revision should be loaded, defaults to FALSE.
   */
  protected function attachLoad(&$queried_entities, $load_revision = FALSE) {
    // Attach fields.
    if ($this->entityInfo['fieldable']) {
      if ($load_revision) {
        $this->fieldLoad($this->entityType, $queried_entities, FIELD_LOAD_REVISION);
      }
      else {
        $this->fieldLoad($this->entityType, $queried_entities, FIELD_LOAD_CURRENT);
      }
    }

    // Call hook_entity_load().
    foreach (module_implements('entity_load') as $module) {
      $function = $module . '_entity_load';
      $function($queried_entities, $this->entityType);
    }
    // Call hook_TYPE_load(). The first argument for hook_TYPE_load() are
    // always the queried entities, followed by additional arguments set in
    // $this->hookLoadArguments.
    $args = array_merge(array($queried_entities), $this->hookLoadArguments);
    foreach (module_implements($this->entityType . '_load') as $module) {
      call_user_func_array($module . '_' . $this->entityType . '_load', $args);
    }
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::create().
   */
  public function create(array $values) {
    $entity_class = $this->entityInfo['class'];
    $entity_class::preCreate($this, $values);

    $entity = new $entity_class($values, $this->entityType);

    // Assign a new UUID if there is none yet.
    if ($this->uuidKey && !isset($entity->{$this->uuidKey})) {
      $uuid = new Uuid();
      $entity->{$this->uuidKey} = $uuid->generate();
    }
    $entity->postCreate($this);

    // Modules might need to add or change the data initially held by the new
    // entity object, for instance to fill-in default values.
    $this->invokeHook('create', $entity);

    return $entity;
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::delete().
   */
  public function delete(array $entities) {
    if (!$entities) {
      // If no IDs or invalid IDs were passed, do nothing.
      return;
    }
    $transaction = $this->database->startTransaction();

    try {
      $entity_class = $this->entityInfo['class'];
      $entity_class::preDelete($this, $entities);
      foreach ($entities as $id => $entity) {
        $this->invokeHook('predelete', $entity);
      }
      $ids = array_keys($entities);

      $this->database->delete($this->entityInfo['base_table'])
        ->condition($this->idKey, $ids, 'IN')
        ->execute();

      if ($this->revisionKey) {
        $this->database->delete($this->revisionTable)
          ->condition($this->idKey, $ids, 'IN')
          ->execute();
      }

      // Reset the cache as soon as the changes have been applied.
      $this->resetCache($ids);

      $entity_class::postDelete($this, $entities);
      foreach ($entities as $id => $entity) {
        $this->invokeFieldMethod('delete', $entity);
        $this->invokeHook('delete', $entity);
      }
      // Ignore slave server temporarily.
      db_ignore_slave();
    }
    catch (\Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::save().
   */
  public function save(EntityInterface $entity) {
    $transaction = $this->database->startTransaction();
    try {
      // Load the stored entity, if any.
      if (!$entity->isNew() && !isset($entity->original)) {
        $entity->original = entity_load_unchanged($this->entityType, $entity->id());
      }

      $entity->preSave($this);
      $this->invokeFieldMethod('preSave', $entity);
      $this->invokeHook('presave', $entity);

      if (!$entity->isNew()) {
        if ($entity->isDefaultRevision()) {
          $return = drupal_write_record($this->entityInfo['base_table'], $entity, $this->idKey);
        }
        else {
          // @todo, should a different value be returned when saving an entity
          // with $isDefaultRevision = FALSE?
          $return = FALSE;
        }
        if ($this->revisionKey) {
          $this->saveRevision($entity);
        }
        $this->resetCache(array($entity->id()));
        $entity->postSave($this, TRUE);
        $this->invokeFieldMethod('update', $entity);
        $this->invokeHook('update', $entity);
      }
      else {
        $return = drupal_write_record($this->entityInfo['base_table'], $entity);
        if ($this->revisionKey) {
          $this->saveRevision($entity);
        }
        // Reset general caches, but keep caches specific to certain entities.
        $this->resetCache(array());

        $entity->enforceIsNew(FALSE);
        $entity->postSave($this, FALSE);
        $this->invokeFieldMethod('insert', $entity);
        $this->invokeHook('insert', $entity);
      }

      // Ignore slave server temporarily.
      db_ignore_slave();
      unset($entity->original);

      return $return;
    }
    catch (\Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Saves an entity revision.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   */
  protected function saveRevision(EntityInterface $entity) {
    // Convert the entity into an array as it might not have the same properties
    // as the entity, it is just a raw structure.
    $record = (array) $entity;

    // When saving a new revision, set any existing revision ID to NULL so as to
    // ensure that a new revision will actually be created.
    if ($entity->isNewRevision() && $record[$this->revisionKey]) {
      $record[$this->revisionKey] = NULL;
    }

    // Cast to object as preSaveRevision() expects one to be compatible with the
    // upcoming NG storage controller.
    $record = (object) $record;
    $entity->preSaveRevision($this, $record);
    $record = (array) $record;

    if ($entity->isNewRevision()) {
      drupal_write_record($this->revisionTable, $record);
      if ($entity->isDefaultRevision()) {
        $this->database->update($this->entityInfo['base_table'])
          ->fields(array($this->revisionKey => $record[$this->revisionKey]))
          ->condition($this->idKey, $entity->id())
          ->execute();
      }
      $entity->setNewRevision(FALSE);
    }
    else {
      drupal_write_record($this->revisionTable, $record, $this->revisionKey);
    }
    // Make sure to update the new revision key for the entity.
    $entity->{$this->revisionKey} = $record[$this->revisionKey];
  }

  /**
   * {@inheritdoc}
   */
  public function baseFieldDefinitions() {
    // @todo: Define abstract once all entity types have been converted.
    return array();
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::getQueryServiceName().
   */
  public function getQueryServiceName() {
    return 'entity.query.field_sql_storage';
  }

  /**
   * {@inheritdoc}
   */
  function doFieldLoad($entity_type, $entities, $age) {
    $load_current = $age == FIELD_LOAD_CURRENT;
    $bundles = array();
    foreach ($entities as $entity) {
      $bundles[$entity->bundle()] = TRUE;
    }
    $fields = array();
    foreach ($bundles as $bundle => $v) {
      foreach ($this->fieldInfo->getBundleInstances($entity_type, $bundle) as $field_name => $instance) {
        $fields[$field_name] = $instance->getField();
      }
    }
    foreach ($fields as $field_name => $field) {
      $table = $load_current ? _field_sql_storage_tablename($field) : _field_sql_storage_revision_tablename($field);

      $query = $this->database->select($table, 't')
        ->fields('t')
        ->condition('entity_type', $entity_type)
        ->condition($load_current ? 'entity_id' : 'revision_id', $ids, 'IN')
        ->condition('langcode', field_available_languages($entity_type, $field), 'IN')
        ->orderBy('delta');

      if (empty($options['deleted'])) {
        $query->condition('deleted', 0);
      }

      $results = $query->execute();

      $delta_count = array();
      foreach ($results as $row) {
        if (!isset($delta_count[$row->entity_id][$row->langcode])) {
          $delta_count[$row->entity_id][$row->langcode] = 0;
        }

        if ($field['cardinality'] == FIELD_CARDINALITY_UNLIMITED || $delta_count[$row->entity_id][$row->langcode] < $field['cardinality']) {
          $item = array();
          // For each column declared by the field, populate the item
          // from the prefixed database column.
          foreach ($field['columns'] as $column => $attributes) {
            $column_name = _field_sql_storage_columnname($field_name, $column);
            // Unserialize the value if specified in the column schema.
            $item[$column] = (!empty($attributes['serialize'])) ? unserialize($row->$column_name) : $row->$column_name;
          }

          // Add the item to the field values for the entity.
          $entities[$row->entity_id]->{$field_name}[$row->langcode][] = $item;
          $delta_count[$row->entity_id][$row->langcode]++;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doFieldInsert(EntityInterface $entity) {
    $this->doFieldWrite($entity, FIELD_STORAGE_INSERT);
  }

  /**
   * {@inheritdoc}
   */
  protected function doFieldUpdate(EntityInterface $entity) {
    $this->doFieldWrite($entity, FIELD_STORAGE_UPDATE);
  }

  /**
   * {@inheritdoc}
   */
  protected function doFieldWrite(EntityInterface $entity, $op) {
    $vid = $entity->getRevisionId();
    $id = $entity->id();
    $bundle = $entity->bundle();
    $entity_type = $entity->entityType();
    if (!isset($vid)) {
      $vid = $id;
    }

    foreach ($this->fieldInfo->getBundleInstances($entity_type, $bundle) as $field_name => $instance) {
      $field = $instance->getField();
      $table_name = _field_sql_storage_tablename($field);
      $revision_name = _field_sql_storage_revision_tablename($field);

      $all_langcodes = field_available_languages($entity_type, $field);
      $field_langcodes = array_intersect($all_langcodes, array_keys((array) $entity->$field_name));

      // Delete and insert, rather than update, in case a value was added.
      if ($op == FIELD_STORAGE_UPDATE) {
        // Delete language codes present in the incoming $entity->$field_name.
        // Delete all language codes if $entity->$field_name is empty.
        $langcodes = !empty($entity->$field_name) ? $field_langcodes : $all_langcodes;
        if ($langcodes) {
          // Only overwrite the field's base table if saving the default revision
          // of an entity.
          if ($entity->isDefaultRevision()) {
            $this->database->delete($table_name)
              ->condition('entity_type', $entity_type)
              ->condition('entity_id', $id)
              ->condition('langcode', $langcodes, 'IN')
              ->execute();
          }
          $this->database->delete($revision_name)
            ->condition('entity_type', $entity_type)
            ->condition('entity_id', $id)
            ->condition('revision_id', $vid)
            ->condition('langcode', $langcodes, 'IN')
            ->execute();
        }
      }

      // Prepare the multi-insert query.
      $do_insert = FALSE;
      $columns = array('entity_type', 'entity_id', 'revision_id', 'bundle', 'delta', 'langcode');
      foreach ($field['columns'] as $column => $attributes) {
        $columns[] = _field_sql_storage_columnname($field_name, $column);
      }
      $query = $this->database->insert($table_name)->fields($columns);
      $revision_query = $this->database->insert($revision_name)->fields($columns);

      foreach ($field_langcodes as $langcode) {
        $items = (array) $entity->{$field_name}[$langcode];
        $delta_count = 0;
        foreach ($items as $delta => $item) {
          // We now know we have someting to insert.
          $do_insert = TRUE;
          $record = array(
            'entity_type' => $entity_type,
            'entity_id' => $id,
            'revision_id' => $vid,
            'bundle' => $bundle,
            'delta' => $delta,
            'langcode' => $langcode,
          );
          foreach ($field['columns'] as $column => $attributes) {
            $column_name = _field_sql_storage_columnname($field_name, $column);
            $value = isset($item[$column]) ? $item[$column] : NULL;
            // Serialize the value if specified in the column schema.
            $record[$column_name] = (!empty($attributes['serialize'])) ? serialize($value) : $value;
          }
          $query->values($record);
          if (isset($vid)) {
            $revision_query->values($record);
          }

          if ($field['cardinality'] != FIELD_CARDINALITY_UNLIMITED && ++$delta_count == $field['cardinality']) {
            break;
          }
        }
      }

      // Execute the query if we have values to insert.
      if ($do_insert) {
        // Only overwrite the field's base table if saving the default revision
        // of an entity.
        if ($entity->isDefaultRevision()) {
          $query->execute();
        }
        $revision_query->execute();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function dofieldDelete(EntityInterface $entity) {
    foreach ($this->fieldInfo->getBundleInstances($entity->getType(), $entity->bundle()) as $instance) {
      $field = $instance->getField();
      $table_name = _field_sql_storage_tablename($field);
      $revision_name = _field_sql_storage_revision_tablename($field);
      $this->database->delete($table_name)
        ->condition('entity_type', $entity->entityType())
        ->condition('entity_id', $entity->id())
        ->execute();
      $this->database->delete($revision_name)
        ->condition('entity_type', $entity->entityType())
        ->condition('entity_id', $entity->id())
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doFieldRevisionDelete(EntityInterface $entity) {
    $vid = $entity->getRevisionId();
    if (isset($vid)) {
      foreach ($this->fieldInfo->getBundleInstances($entity->getType(), $entity->bundle()) as $instance) {
        $revision_name = _field_sql_storage_revision_tablename($instance->getField());
        $this->database->delete($revision_name)
          ->condition('entity_type', $entity->entityType())
          ->condition('entity_id', $entity->id())
          ->condition('revision_id', $vid)
          ->execute();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function handleBundleRename($bundle, $bundle_new) {
    // We need to account for deleted or inactive fields and instances.
    $instances = field_read_instances(array('entity_type' => $this->entityType, 'bundle' => $bundle_new), array('include_deleted' => TRUE, 'include_inactive' => TRUE));
    foreach ($instances as $instance) {
      $field = field_info_field_by_id($instance['field_id']);
      if ($field['storage']['type'] == 'field_sql_storage') {
        $table_name = _field_sql_storage_tablename($field);
        $revision_name = _field_sql_storage_revision_tablename($field);
        $this->database->update($table_name)
          ->fields(array('bundle' => $bundle_new))
          ->condition('entity_type', $this->entityType)
          ->condition('bundle', $bundle)
          ->execute();
        $this->database->update($revision_name)
          ->fields(array('bundle' => $bundle_new))
          ->condition('entity_type', $this->entityType)
          ->condition('bundle', $bundle)
          ->execute();
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function storageType() {
    return 'sql';
  }

  /**
   * {@inheritdoc}
   */
  public function handleInsertField(Field $field) {
    $schema = $this->fieldSchema($field);
    foreach ($schema as $name => $table) {
      $this->database->schema()->createTable($name, $table);
    }
  }

  public function handleUpdateField(Field $field, Field $original) {
    if (!$field->hasData()) {
      // There is no data. Re-create the tables completely.

      if ($this->database->supportsTransactionalDDL()) {
        // If the database supports transactional DDL, we can go ahead and rely
        // on it. If not, we will have to rollback manually if something fails.
        $transaction = $this->database->startTransaction();
      }

      try {
        $original_schema = $this->fieldSchema($original);
        foreach ($original_schema as $name => $table) {
          $this->database->schema()->dropTable($name, $table);
        }
        $schema = $this->fieldSchema($field);
        foreach ($schema as $name => $table) {
          $this->database->schema()->createTable($name, $table);
        }
      }
      catch (\Exception $e) {
        if ($this->database->supportsTransactionalDDL()) {
          $transaction->rollback();
        }
        else {
          // Recreate tables.
          $original_schema = $this->fieldSchema($original);
          foreach ($original_schema as $name => $table) {
            if (!$this->database->schema()->tableExists($name)) {
              $this->database->schema()->createTable($name, $table);
            }
          }
        }
        throw $e;
      }
    }
    else {
      // There is data, so there are no column changes. Drop all the
      // prior indexes and create all the new ones, except for all the
      // priors that exist unchanged.
      $table = _field_sql_storage_tablename($original);
      $revision_table = _field_sql_storage_revision_tablename($original);

      $schema = $field->getSchema();
      $original_schema = $original->getSchema();

      foreach ($original_schema['indexes'] as $name => $columns) {
        if (!isset($schema['indexes'][$name]) || $columns != $schema['indexes'][$name]) {
          $real_name = _field_sql_storage_indexname($field['field_name'], $name);
          $this->database->schema()->dropIndex($table, $real_name);
          $this->database->schema()->dropIndex($revision_table, $real_name);
        }
      }
      $table = _field_sql_storage_tablename($field);
      $revision_table = _field_sql_storage_revision_tablename($field);
      foreach ($schema['indexes'] as $name => $columns) {
        if (!isset($original_schema['indexes'][$name]) || $columns != $original_schema['indexes'][$name]) {
          $real_name = _field_sql_storage_indexname($field['field_name'], $name);
          $real_columns = array();
          foreach ($columns as $column_name) {
            // Indexes can be specified as either a column name or an array with
            // column name and length. Allow for either case.
            if (is_array($column_name)) {
              $real_columns[] = array(
                _field_sql_storage_columnname($field['field_name'], $column_name[0]),
                $column_name[1],
              );
            }
            else {
              $real_columns[] = _field_sql_storage_columnname($field['field_name'], $column_name);
            }
          }
          $this->database->schema()->addIndex($table, $real_name, $real_columns);
          $this->database->schema()->addIndex($revision_table, $real_name, $real_columns);
        }
      }
    }
  }

  protected function fieldSchema(Field $field) {
    $deleted = $field['deleted'] ? 'deleted ' : '';
    $current = array(
      'description' => "Data storage for {$deleted}field {$field['id']} ({$field['field_name']})",
      'fields' => array(
        'entity_type' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The entity type this data is attached to',
        ),
        'bundle' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The field instance bundle to which this row belongs, used when deleting a field instance',
        ),
        'deleted' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'A boolean indicating whether this data item has been deleted'
        ),
        'entity_id' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'The entity id this data is attached to',
        ),
        'revision_id' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
          'description' => 'The entity revision id this data is attached to, or NULL if the entity type is not versioned',
        ),
        // @todo Consider storing langcode as integer.
        'langcode' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The language code for this data item.',
        ),
        'delta' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'The sequence number for this data item, used for multi-value fields',
        ),
      ),
      'primary key' => array('entity_type', 'entity_id', 'deleted', 'delta', 'langcode'),
      'indexes' => array(
        'entity_type' => array('entity_type'),
        'bundle' => array('bundle'),
        'deleted' => array('deleted'),
        'entity_id' => array('entity_id'),
        'revision_id' => array('revision_id'),
        'langcode' => array('langcode'),
      ),
    );

    $schema = $field->getSchema();

    // Add field columns.
    foreach ($schema['columns'] as $column_name => $attributes) {
      $real_name = _field_sql_storage_columnname($field['field_name'], $column_name);
      $current['fields'][$real_name] = $attributes;
    }

    // Add indexes.
    foreach ($schema['indexes'] as $index_name => $columns) {
      $real_name = _field_sql_storage_indexname($field['field_name'], $index_name);
      foreach ($columns as $column_name) {
        // Indexes can be specified as either a column name or an array with
        // column name and length. Allow for either case.
        if (is_array($column_name)) {
          $current['indexes'][$real_name][] = array(
            _field_sql_storage_columnname($field['field_name'], $column_name[0]),
            $column_name[1],
          );
        }
        else {
          $current['indexes'][$real_name][] = _field_sql_storage_columnname($field['field_name'], $column_name);
        }
      }
    }

    // Add foreign keys.
    foreach ($schema['foreign keys'] as $specifier => $specification) {
      $real_name = _field_sql_storage_indexname($field['field_name'], $specifier);
      $current['foreign keys'][$real_name]['table'] = $specification['table'];
      foreach ($specification['columns'] as $column_name => $referenced) {
        $sql_storage_column = _field_sql_storage_columnname($field['field_name'], $column_name);
        $current['foreign keys'][$real_name]['columns'][$sql_storage_column] = $referenced;
      }
    }

    // Construct the revision table.
    $revision = $current;
    $revision['description'] = "Revision archive storage for {$deleted}field {$field['id']} ({$field['field_name']})";
    $revision['primary key'] = array('entity_type', 'entity_id', 'revision_id', 'deleted', 'delta', 'langcode');
    $revision['fields']['revision_id']['not null'] = TRUE;
    $revision['fields']['revision_id']['description'] = 'The entity revision id this data is attached to';

    return array(
      _field_sql_storage_tablename($field) => $current,
      _field_sql_storage_revision_tablename($field) => $revision,
    );

  }
}
