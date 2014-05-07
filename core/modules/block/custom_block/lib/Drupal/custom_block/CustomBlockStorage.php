<?php
/**
 * @file
 * Contains \Drupal\custom_block\CustomBlockStorage.
 */

namespace Drupal\custom_block;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;

/**
 * Provides storage for the 'custom_block' entity type.
 */
class CustomBlockStorage extends ContentEntityDatabaseStorage {

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = parent::getSchema();

    $schema['custom_block']['unique keys'] += array(
      'custom_block__info' => array('info'),
    );

    return $schema;
  }

}
