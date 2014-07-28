<?php

/**
 * @file
 * Definition of Drupal\file\FileStorage.
 */

namespace Drupal\file;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;

/**
 * File storage for files.
 */
class FileStorage extends ContentEntityDatabaseStorage implements FileStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function spaceUsed($uid = NULL, $status = FILE_STATUS_PERMANENT) {
    $query = $this->database->select($this->entityType->getBaseTable(), 'f')
      ->condition('f.status', $status);
    $query->addExpression('SUM(f.filesize)', 'filesize');
    if (isset($uid)) {
      $query->condition('f.uid', $uid);
    }
    return $query->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  protected function schemaHandler() {
    if (!isset($this->schemaHandler)) {
      $this->schemaHandler = new FileSchemaHandler($this->entityManager, $this->entityType, $this, $this->database);
    }
    return $this->schemaHandler;
  }

}
