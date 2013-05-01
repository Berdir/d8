<?php

/**
 * @file
 * Hooks for file module.
 */


/**
 * Act on a newly created file.
 *
 * This hook runs after a new file object has just been instantiated. It can be
 * used to set initial values, e.g. to provide defaults.
 *
 * @param \Drupal\file\Plugin\Core\Entity\File $file
 *   The file object.
 */
function hook_file_create(\Drupal\file\Plugin\Core\Entity\File $file) {
  if (!isset($file->foo)) {
    $file->foo = 'some_initial_value';
  }
}

/**
 * Load additional information into file entities.
 *
 * file_load_multiple() calls this hook to allow modules to load
 * additional information into each file.
 *
 * @param $files
 *   An array of file entities, indexed by fid.
 *
 * @see file_load_multiple()
 * @see file_load()
 */
function hook_file_load($files) {
  // Add the upload specific data into the file entity.
  $result = db_query('SELECT * FROM {upload} u WHERE u.fid IN (:fids)', array(':fids' => array_keys($files)))->fetchAll(PDO::FETCH_ASSOC);
  foreach ($result as $record) {
    foreach ($record as $key => $value) {
      $files[$record['target_id']]->$key = $value;
    }
  }
}

/**
 * Check that files meet a given criteria.
 *
 * This hook lets modules perform additional validation on files. They're able
 * to report a failure by returning one or more error messages.
 *
 * @param \Drupal\file\FileInterface $file
 *   The file entity being validated.
 * @return
 *   An array of error messages. If there are no problems with the file return
 *   an empty array.
 *
 * @see file_validate()
 */
function hook_file_validate(Drupal\file\FileInterface $file) {
  $errors = array();

  if (!$file->getFilename()) {
    $errors[] = t("The file's name is empty. Please give a name to the file.");
  }
  if (strlen($file->getFilename()) > 255) {
    $errors[] = t("The file's name exceeds the 255 characters limit. Please rename the file and try again.");
  }

  return $errors;
}

/**
 * Act on a file being inserted or updated.
 *
 * This hook is called when a file has been added to the database. The hook
 * doesn't distinguish between files created as a result of a copy or those
 * created by an upload.
 *
 * @param \Drupal\file\FileInterface $file
 *   The file entity that is about to be created or updated.
 */
function hook_file_presave(Drupal\file\FileInterface $file) {
  // Change the file timestamp to an hour prior.
  $file->timestamp -= 3600;
}

/**
 * Respond to a file being added.
 *
 * This hook is called after a file has been added to the database. The hook
 * doesn't distinguish between files created as a result of a copy or those
 * created by an upload.
 *
 * @param \Drupal\file\FileInterface $file
 *   The file that has been added.
 */
function hook_file_insert(Drupal\file\FileInterface $file) {
  // Add a message to the log, if the file is a jpg
  $validate = file_validate_extensions($file, 'jpg');
  if (empty($validate)) {
    watchdog('file', 'A jpg has been added.');
  }
}

/**
 * Respond to a file being updated.
 *
 * This hook is called when an existing file is saved.
 *
 * @param \Drupal\file\FileInterface $file
 *   The file that has just been updated.
 */
function hook_file_update(Drupal\file\FileInterface $file) {
  // Make sure that the file name starts with the owner's user name.
  if (strpos($file->getFilename(), $file->getOwner()->name) !== 0) {
    $old_filename = $file->getFilename();
    $file->setFilename($file->getOwner()->name . '_' . $file->getFilename());
    $file->save();

    watchdog('file', t('%source has been renamed to %destination', array('%source' => $old_filename, '%destination' => $file->getFilename())));
  }
}

/**
 * Respond to a file that has been copied.
 *
 * @param \Drupal\file\FileInterface $file
 *   The newly copied file entity.
 * @param \Drupal\file\FileInterface $source
 *   The original file before the copy.
 *
 * @see file_copy()
 */
function hook_file_copy(Drupal\file\FileInterface $file, Drupal\file\FileInterface $source) {
  // Make sure that the file name starts with the owner's user name.
  if (strpos($file->getFilename(), $file->getOwner()->name) !== 0) {
    $file->setFilename($file->getOwner()->name . '_' . $file->getFilename());
    $file->save();

    watchdog('file', t('Copied file %source has been renamed to %destination', array('%source' => $source->filename, '%destination' => $file->getFilename())));
  }
}

/**
 * Respond to a file that has been moved.
 *
 * @param \Drupal\file\FileInterface $file
 *   The updated file entity after the move.
 * @param \Drupal\file\FileInterface $source
 *   The original file entity before the move.
 *
 * @see file_move()
 */
function hook_file_move(Drupal\file\FileInterface $file, Drupal\file\FileInterface $source) {
  // Make sure that the file name starts with the owner's user name.
  if (strpos($file->getFilename(), $file->getOwner()->name) !== 0) {
    $file->setFilename($file->getOwner()->name . '_' . $file->getFilename());
    $file->save();

    watchdog('file', t('Moved file %source has been renamed to %destination', array('%source' => $source->filename, '%destination' => $file->getFilename())));
  }
}

/**
 * Act prior to file deletion.
 *
 * This hook is invoked when deleting a file before the file is removed from the
 * filesystem and before its records are removed from the database.
 *
 * @param \Drupal\file\FileInterface $file
 *   The file that is about to be deleted.
 *
 * @see hook_file_delete()
 * @see Drupal\file\FileStorageController::delete()
 * @see upload_file_delete()
 */
function hook_file_predelete(Drupal\file\FileInterface $file) {
  // Delete all information associated with the file.
  db_delete('upload')->condition('fid', $file->id())->execute();
}

/**
 * Respond to file deletion.
 *
 * This hook is invoked after the file has been removed from
 * the filesystem and after its records have been removed from the database.
 *
 * @param \Drupal\file\FileInterface $file
 *   The file that has just been deleted.
 *
 * @see hook_file_predelete()
 * @see Drupal\file\FileStorageController::delete()
 */
function hook_file_delete(Drupal\file\FileInterface $file) {
  // Delete all information associated with the file.
  db_delete('upload')->condition('fid', $file->id())->execute();
}
