<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;

/**
 * Interface for sources providing an entity.
 */
interface SourceEntityInterface {

  /**
   * @return bool
   *   TRUE when the bundle_migration key is required.
   */
  public function bundleMigrationRequired();

  /**
   * The entity type id (user, node etc).
   *
   * This function is used when bundleMigrationRequired() is FALSE.
   *
   * @return string
   */
    public function entityTypeId();

}
