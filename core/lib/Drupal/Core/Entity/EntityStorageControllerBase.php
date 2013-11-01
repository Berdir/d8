<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityStorageControllerBase.
 */

namespace Drupal\Core\Entity;

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
  public function entityType() {
    return $this->entityType;
  }

  /**
   * {@inheritdoc}
   */
  public function entityInfo() {
    return $this->entityInfo;
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
   * Attaches data to entities upon loading.
   *
   * @param $queried_entities
   *   Associative array of query results, keyed on the entity ID.
   * @param $revision_id_id
   *   ID of the revision that was loaded, or FALSE if the most current revision
   *   was loaded.
   */
  protected function postLoad(array &$queried_entities, $revision_id_id = FALSE) {
    $class = isset($this->entityInfo['class']) ? $this->entityInfo['class']: $this->entityClass;
    $class::postLoad($this, $queried_entities, $revision_id_id);
  }

}
