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
  public function preSaveRevision(EntityInterface $revision) {
    // Allow for predefined revision ids.
    if (!empty($revision->use_provided_revision_id) && !empty($revision->old_ftvid)) {
      $revision->ftvid = $revision->old_ftvid;
    }
  }


}
