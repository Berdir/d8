<?php
/**
 * @file
 * Definition of Drupal\entity_test\TestEntityStorageController.
 */

namespace Drupal\entity_test;
use Drupal\entity\EntityInterface;
use Drupal\entity\EntityStorageException;

/**
 * Storage controller for test entities.
 */
class TestEntityStorageController extends \Drupal\entity\DatabaseStorageController {

  protected function attachLoad(&$queried_entities, $revision_id = FALSE) {
    // @todo: Clean-up by loading into a stdclass $record object first.
    foreach ($queried_entities as $entity) {
      $entity->set('name', array(0 => array('value' => isset($entity->name) ? $entity->name : NULL)));
      $entity->set('user', array(0 => array('id' => $entity->uid)));

      // Make the magic getter work ...
      unset($entity->name);
    }

    parent::attachLoad($queried_entities, $revision_id);
  }

  public function save(EntityInterface $entity) {
    $transaction = db_transaction();
    try {
      // Load the stored entity, if any.
      if (!$entity->isNew() && !isset($entity->original)) {
        $entity->original = entity_load_unchanged($this->entityType, $entity->id());
      }

      $this->preSave($entity);
      $this->invokeHook('presave', $entity);

      // Create the database record to be saved.
      $record['id'] = $entity->id();
      $record['name'] = $entity->name->value;
      $record['uid'] = $entity->user->id;
      foreach ($entity as $name => $property) {
        if (!isset($record[$name])) {
          $record[$name] = $property->toArray();
        }
      }

      if (!$entity->isNew()) {
        $return = drupal_write_record($this->entityInfo['base table'], $record, 'id');
        $this->resetCache(array($entity->{$this->idKey}));
        $this->postSave($entity, TRUE);
        $this->invokeHook('update', $entity);
      }
      else {
        $return = drupal_write_record($this->entityInfo['base table'], $record);
        // Reset general caches, but keep caches specific to certain entities.
        $this->resetCache(array());

        $entity->{$this->idKey} = $record['id'];
        $entity->enforceIsNew(FALSE);
        $this->postSave($entity, FALSE);
        $this->invokeHook('insert', $entity);
      }

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
   * Implements \Drupal\entity\EntityStorageControllerInterface.
   */
  public function basePropertyDefinitions() {
    $properties['name'] = array(
      'label' => t('Name'),
      'description' => ('The name of the test entity.'),
      'type' => 'text_item',
    );
    $properties['user'] = array(
      'type' => 'entityreference_item',
      'entity type' => 'user',
      'label' => t('User'),
      'description' => t('The associated user.'),
    );
    return $properties;
  }
}
