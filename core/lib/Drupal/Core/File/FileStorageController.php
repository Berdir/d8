<?php

/**
 * @file
 * Definition of Drupal\Core\File\FileStorageController.
 */

namespace Drupal\Core\File;

use EntityDatabaseStorageController;
use EntityInterface;

/**
 * File storage controller for files.
 */
class FileStorageController extends EntityDatabaseStorageController {

  /**
   * Overrides EntityDatabaseStorageController::presave().
   */
  protected function preSave(EntityInterface $entity) {
    $entity->timestamp = REQUEST_TIME;
    $entity->filesize = filesize($entity->uri);
  }

  /**
   * Overrides EntityDatabaseStorageController::delete().
   *
   * file_usage_list() is called to determine if the file is being used by any
   * modules. If the file is being used the delete will be canceled.
   */
  public function delete($ids) {
    foreach (file_load_multiple($ids) as $file) {
      if (!file_valid_uri($file->uri)) {
        if (($realpath = drupal_realpath($file->uri)) !== FALSE) {
          watchdog('file', 'File %file (%realpath) could not be deleted because it is not a valid URI. This may be caused by improper use of file_delete() or a missing stream wrapper.', array('%file' => $file->uri, '%realpath' => $realpath));
        }
        else {
          watchdog('file', 'File %file could not be deleted because it is not a valid URI. This may be caused by improper use of file_delete() or a missing stream wrapper.', array('%file' => $file->uri));
        }
        drupal_set_message(t('The specified file %file could not be deleted because it is not a valid URI. More information is available in the system log.', array('%file' => $file->uri)), 'error');
        continue;
      }

      // If any module still has a usage entry in the file_usage table, the file
      // will not be deleted, but file_delete() will return a populated array
      // that tests as TRUE.
      if ($references = file_usage_list($file)) {
        continue;
      }

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
