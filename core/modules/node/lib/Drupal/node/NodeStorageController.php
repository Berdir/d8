<?php

/**
 * @file
 * Definition of Drupal\node\NodeStorageController.
 */

namespace Drupal\node;

use Drupal\entity\DatabaseStorageController;
use Drupal\entity\EntityInterface;
use Drupal\entity\EntityStorageException;
use Exception;

/**
 * Controller class for nodes.
 *
 * This extends the Drupal\entity\DatabaseStorageController class, adding
 * required special handling for node entities.
 */
class NodeStorageController extends DatabaseStorageController {

  /**
   * Overrides Drupal\entity\DatabaseStorageController::create().
   */
  public function create(array $values) {
    $node = parent::create($values);

    // Set the created time to now.
    if (empty($node->created)) {
      $node->created = REQUEST_TIME;
    }

    return $node;
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::delete().
   */
  public function delete($ids) {
    $entities = $ids ? $this->load($ids) : FALSE;
    if (!$entities) {
      // If no IDs or invalid IDs were passed, do nothing.
      return;
    }
    $transaction = db_transaction();

    try {
      $this->preDelete($entities);
      foreach ($entities as $id => $entity) {
        $this->invokeHook('predelete', $entity);
      }
      $ids = array_keys($entities);

      db_delete($this->entityInfo['base table'])
        ->condition($this->idKey, $ids, 'IN')
        ->execute();

      if ($this->revisionKey) {
        db_delete($this->revisionTable)
          ->condition($this->idKey, $ids, 'IN')
          ->execute();
      }

      // Reset the cache as soon as the changes have been applied.
      $this->resetCache($ids);

      $this->postDelete($entities);
      foreach ($entities as $id => $entity) {
        $this->invokeHook('delete', $entity);
      }
      // Ignore slave server temporarily.
      db_ignore_slave();
    }
    catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw new EntityStorageException($e->getMessage, $e->getCode, $e);
    }
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::save().
   */
  public function save(EntityInterface $entity) {
    $transaction = db_transaction();
    try {
      // Load the stored entity, if any.
      if (!$entity->isNew() && !isset($entity->original)) {
        $entity->original = entity_load_unchanged($this->entityType, $entity->id());
      }

      $this->preSave($entity);
      $this->invokeHook('presave', $entity);

      if ($entity->isNew()) {
        $op = 'insert';
        $return = drupal_write_record($this->entityInfo['base table'], $entity);
        $entity->enforceIsNew(FALSE);
      }
      else {
        $op = 'update';
        $return = drupal_write_record($this->entityInfo['base table'], $entity, $this->idKey);
      }

      if ($this->revisionKey) {
        $this->saveRevision($entity);
      }

      // Reset general caches, but keep caches specific to certain entities.
      $this->resetCache($op == 'update' ? array($entity->{$this->idKey}): array());

      $this->postSave($entity, $op == 'update');
      $this->invokeHook($op, $entity);

      // Ignore slave server temporarily.
      db_ignore_slave();
      unset($entity->original);

      return $return;
    }
    catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Saves a node revision.
   *
   * @param Drupal\entity\EntityInterface $node
   *   The node entity.
   */
  protected function saveRevision(EntityInterface $entity) {
    $record = clone $entity;
    $record->uid = $entity->revision_uid;
    $record->timestamp = $entity->revision_timestamp;

    if (empty($entity->{$this->revisionKey}) || !empty($entity->revision)) {
      drupal_write_record($this->revisionTable, $record);
      db_update($this->entityInfo['base table'])
        ->fields(array($this->revisionKey => $record->{$this->revisionKey}))
        ->condition($this->idKey, $entity->{$this->idKey})
        ->execute();
    }
    else {
      drupal_write_record($this->revisionTable, $record, $this->revisionKey);
    }
    // Make sure to update the new revision key for the entity.
    $entity->{$this->revisionKey} = $record->{$this->revisionKey};

    // Mark this revision as the current one.
    $entity->isCurrentRevision = TRUE;
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::attachLoad().
   */
  protected function attachLoad(&$nodes, $load_revision = FALSE) {
    // Create an array of nodes for each content type and pass this to the
    // object type specific callback.
    $typed_nodes = array();
    foreach ($nodes as $id => $entity) {
      $typed_nodes[$entity->type][$id] = $entity;
    }

    // Call object type specific callbacks on each typed array of nodes.
    foreach ($typed_nodes as $node_type => $nodes_of_type) {
      if (node_hook($node_type, 'load')) {
        $function = node_type_get_base($node_type) . '_load';
        $function($nodes_of_type);
      }
    }
    // Besides the list of nodes, pass one additional argument to
    // hook_node_load(), containing a list of node types that were loaded.
    $argument = array_keys($typed_nodes);
    $this->hookLoadArguments = array($argument);
    parent::attachLoad($nodes, $load_revision);
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::buildQuery().
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    // Ensure that uid is taken from the {node} table,
    // alias timestamp to revision_timestamp and add revision_uid.
    $query = parent::buildQuery($ids, $revision_id);
    $fields =& $query->getFields();
    unset($fields['timestamp']);
    $query->addField('revision', 'timestamp', 'revision_timestamp');
    $fields['uid']['table'] = 'base';
    $query->addField('revision', 'uid', 'revision_uid');
    return $query;
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::invokeHook().
   */
  protected function invokeHook($hook, EntityInterface $node) {
    if ($hook == 'insert' || $hook == 'update') {
      node_invoke($node, $hook);
    }
    else if ($hook == 'predelete') {
      // 'delete' is triggered in 'predelete' is here to preserve hook ordering
      // from Drupal 7.
      node_invoke($node, 'delete');
    }

    parent::invokeHook($hook, $node);

    if ($hook == 'presave') {
      if ($node->isNew() || !empty($node->revision)) {
        // When inserting either a new node or a new node revision, $node->log
        // must be set because {node_revision}.log is a text column and therefore
        // cannot have a default value. However, it might not be set at this
        // point (for example, if the user submitting a node form does not have
        // permission to create revisions), so we ensure that it is at least an
        // empty string in that case.
        // @todo: Make the {node_revision}.log column nullable so that we can
        // remove this check.
        if (!isset($node->log)) {
          $node->log = '';
        }
      }
      elseif (!isset($node->log) || $node->log === '') {
        // If we are updating an existing node without adding a new revision, we
        // need to make sure $node->log is unset whenever it is empty. As long as
        // $node->log is unset, drupal_write_record() will not attempt to update
        // the existing database column when re-saving the revision; therefore,
        // this code allows us to avoid clobbering an existing log entry with an
        // empty one.
        unset($node->log);
      }

      // When saving a new node revision, unset any existing $node->vid so as to
      // ensure that a new revision will actually be created, then store the old
      // revision ID in a separate property for use by node hook implementations.
      if (!$node->isNew() && !empty($node->revision) && $node->vid) {
        $node->old_vid = $node->vid;
        $node->vid = NULL;
      }
    }
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::preSave().
   */
  protected function preSave(EntityInterface $node) {
    // Before saving the node, set changed and revision times.
    $node->changed = REQUEST_TIME;

    if ($this->revisionKey && !empty($node->revision)) {
      $node->revision_timestamp = REQUEST_TIME;

      if (!isset($node->revision_uid)) {
        $node->revision_uid = $GLOBALS['user']->uid;
      }
    }
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postSave().
   */
  function postSave(EntityInterface $node, $update) {
    node_access_acquire_grants($node, $update);
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::preDelete().
   */
  function preDelete($entities) {
    if (module_exists('search')) {
      foreach ($entities as $id => $entity) {
        search_reindex($entity->nid, 'node');
      }
    }
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postDelete().
   */
  protected function postDelete($nodes) {
    // Delete values from other tables also referencing this node.
    $ids = array_keys($nodes);

    db_delete('history')
      ->condition('nid', $ids, 'IN')
      ->execute();
    db_delete('node_access')
      ->condition('nid', $ids, 'IN')
      ->execute();
  }
}
