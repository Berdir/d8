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
   * Overrides Drupal\entity\EntityDatabaseStorageController::create().
   */
  public function create(array $values) {
    // Automatically detect filename if not set.
    if (!isset($values['filename']) && isset($values['uri'])) {
      $values['filename'] = drupal_basename($values['uri']);
    }

    // Automatically detect filemime if not set.
    if (!isset($values['filemime']) && isset($values['uri'])) {
      $values['filemime'] = file_get_mimetype($values['uri']);
    }
    return parent::create($values);
  }

  /**
   * Overrides Drupal\entity\EntityDatabaseStorageController::presave().
   */
  protected function preSave(EntityInterface $entity) {
    $entity->timestamp = REQUEST_TIME;
    $entity->filesize = filesize($entity->uri);
    if (!isset($entity->langcode)) {
      // Default the file's language code to none, because files are language
      // neutral more often than language dependent. Until we have better flexible
      // settings.
      // @todo See http://drupal.org/node/258785 and followups.
      $entity->langcode = LANGUAGE_NOT_SPECIFIED;
    }
  }

  /**
   * Overrides Drupal\entity\EntityDatabaseStorageController::delete().
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
