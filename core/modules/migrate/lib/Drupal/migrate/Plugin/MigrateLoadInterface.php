<?php
/**
 * @file
 * Contains
 */
namespace Drupal\migrate\Plugin;

interface MigrateLoadInterface {

  /**
   * Load an additional migration.
   *
   * @param $sub_id
   *   For example, when loading d6_node:article, this will be article.
   * @return \Drupal\migrate\Entity\MigrationInterface
   */
  public function load($sub_id);

  /**
   * Load additional migrations.
   *
   * @param $sub_ids
   *   For example, when loading d6_node:article, sub_id will be article.
   *   If NULL then load all sub-migrations.
   * @return \Drupal\migrate\Entity\MigrationInterface[]
   */
  public function loadMultiple(array $sub_ids = NULL);

}
