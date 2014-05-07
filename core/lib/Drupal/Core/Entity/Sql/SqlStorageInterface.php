<?php
/**
 * @file
 * Contains \Drupal\Core\Entity\Sql\SqlStorageInterface.
 */

namespace Drupal\Core\Entity\Sql;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * A common interface for SQL-based storage controllers.
 */
interface SqlStorageInterface extends EntityStorageInterface {

  /**
   * Gets a table mapping for the entity's tables.
   *
   * @return \Drupal\Core\Entity\Sql\TableMappingInterface
   *   A table mapping object for the entity's tables.
   */
  public function getTableMapping();

}
