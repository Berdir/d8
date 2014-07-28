<?php

/**
 * @file
 * Contains \Drupal\block_content\BlockContentStorage.
 */

namespace Drupal\block_content;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;

/**
 * Provides storage for the 'block_content' entity type.
 */
class BlockContentStorage extends ContentEntityDatabaseStorage {

  /**
   * {@inheritdoc}
   */
  protected function schemaHandler() {
    if (!isset($this->schemaHandler)) {
      $this->schemaHandler = new BlockContentSchemaHandler($this->entityManager, $this->entityType, $this, $this->database);
    }
    return $this->schemaHandler;
  }

}
