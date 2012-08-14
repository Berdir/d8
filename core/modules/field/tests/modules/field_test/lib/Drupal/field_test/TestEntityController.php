<?php

/**
 * @file
 * Definition of Drupal\field_test\TestEntityController.
 */

namespace Drupal\field_test;

use Drupal\entity\DatabaseStorageController;
use Drupal\entity\EntityInterface;

/**
 * Controller class for the test entity entity types.
 */
class TestEntityController extends DatabaseStorageController {

  /**
   * Overrides Drupal\entity\DatabaseStorageController::preSaveRevision().
   */
  public function preSave(EntityInterface $revision) {
    // Prepare for a new revision.
    if (!$entity->isNew() && !empty($entity->revision)) {
      $entity->old_ftvid = $entity->ftvid;
      $entity->ftvid = NULL;
    }
  }


}
