<?php
/**
 * @file
 * Contains \Drupal\migrate\MigrateBuildDependencyInterface.
 */

namespace Drupal\migrate;


interface MigrateBuildDependencyInterface {

  /**
   * Builds a dependency tree for the migrations and set their order.
   *
   * @param array $migrations
   *   Array of loaded migrations with their declared dependencies.
   *
   * @return array
   */
  public function buildDependencyMigration(array $migrations);
}
