<?php
/**
 * @file
 * Contains \Drupal\Core\Entity\Schema\ContentEntitySchemaHandlerInterface.
 */
namespace Drupal\Core\Entity\Schema;

/**
 * A common interface for building the storage schema for content entities.
 */
interface ContentEntitySchemaHandlerInterface {

  /**
   * Gets the full schema array for a given entity type.
   *
   * @return array
   *   A schema array for the entity type's tables.
   */
  public function getSchema();

}
