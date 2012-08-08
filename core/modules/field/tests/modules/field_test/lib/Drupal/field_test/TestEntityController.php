<?php

/**
 * @file
 * Definition of Drupal\field_test\TestEntityController.
 */

namespace Drupal\field_test;

use Drupal\entity\DatabaseStorageController;
use Drupal\entity\EntityInterface;

/**
 * Controller class for the test_entity_bundle entity type.
 */
class TestEntityController extends DatabaseStorageController {

  public function preSave(EntityInterface $entity) {
    if (!$entity->isNew() && !empty($entity->revision)) {
      $entity->old_ftvid = $entity->ftvid;
      $entity->ftvid = NULL;
    }
  }

  public function postSave(EntityInterface $entity, $update) {
    if ($entity->entityType() == 'test_entity') {
      $update_entity = TRUE;
      if (!$update || !empty($entity->revision)) {
        drupal_write_record('test_entity_revision', $entity);
      }
      else {
        drupal_write_record('test_entity_revision', $entity, 'ftvid');
        $update_entity = FALSE;
      }

      if ($update_entity) {
        db_update('test_entity')
          ->fields(array('ftvid' => $entity->ftvid))
          ->condition('ftid', $entity->ftid)
          ->execute();
      }
    }
  }

  public function create(array $values) {
    $entity = parent::create($values);
    $entity->enforceIsNew();
    return $entity;
  }
}
