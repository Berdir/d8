<?php
/**
 * @file
 * Contains
 */
namespace Drupal\migrate\Plugin;

use Drupal\Core\Entity\EntityStorageControllerInterface;

interface MigrateLoadInterface {

  /**
   * Load an additional migration.
   *
   * @param EntityStorageControllerInterface $storage_controller
   *   The migration storage controller.
   * @param $sub_id
   *   For example, when loading d6_node:article, this will be article.
   * @return \Drupal\migrate\Entity\MigrationInterface
   */
  public function load(EntityStorageControllerInterface $storage_controller, $sub_id);

  /**
   * Load additional migrations.
   *
   * @param EntityStorageControllerInterface $storage_controller
   *   The migration storage controller.
   * @param $sub_ids
   *   For example, when loading d6_node:article, sub_id will be article.
   *   If NULL then load all sub-migrations.
   * @return \Drupal\migrate\Entity\MigrationInterface[]
   */
  public function loadMultiple(EntityStorageControllerInterface $storage_controller, array $sub_ids = NULL);

}
