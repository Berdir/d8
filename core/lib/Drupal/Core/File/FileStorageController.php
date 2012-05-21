<?php

/**
 * @file
 * Definition of Drupal\Core\File\FileStorageController.
 */

namespace Drupal\Core\File;

use Drupal\entity\EntityDatabaseStorageController;
use Drupal\entity\EntityInterface;

/**
 * File storage controller for files.
 */
class FileStorageController extends EntityDatabaseStorageController {

  /**
   * Overrides Drupal\entity\EntityDatabaseStorageController::presave().
   */
  protected function preSave(EntityInterface $entity) {
    $entity->timestamp = REQUEST_TIME;
    $entity->filesize = filesize($entity->uri);
  }

  /**
   * Overrides Drupal\entity\EntityDatabaseStorageController::delete().
   *
   * file_usage_list() is called to determine if the file is being used by any
   * modules. If the file is being used the delete will be canceled.
   */
  public function delete($ids) {
    foreach (file_load_multiple($ids) as $file) {
      // Let other modules clean up any references to the file prior to deletion.
      module_invoke_all('file_predelete', $file);
      module_invoke_all('entity_predelete', $file, 'file');

      // Make sure the file is deleted before removing its row from the
      // database, so UIs can still find the file in the database.
      if (file_unmanaged_delete($file->uri)) {
        db_delete('file_managed')->condition('fid', $file->fid)->execute();
        db_delete('file_usage')->condition('fid', $file->fid)->execute();

        // Let other modules respond to file deletion.
        module_invoke_all('file_delete', $file);
        module_invoke_all('entity_delete', $file, 'file');
      }
    }
  }

}
