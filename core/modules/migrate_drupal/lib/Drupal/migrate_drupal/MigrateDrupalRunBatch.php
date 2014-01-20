<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\MigrateDrupalRunBatch.
 */

namespace Drupal\migrate_drupal;

use Drupal\Core\Database\Database;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;

class MigrateDrupalRunBatch {

  /**
   * @param $initial_ids
   *   The initial migration IDs.
   * @param $db_spec
   *   The database specification pointing to the old Drupal database.
   * @param $context
   *   The batch context.
   */
  public static function run($initial_ids, $db_spec, &$context) {
    Database::addConnectionInfo('migrate', 'default', $db_spec);
    if (!isset($context['sandbox']['migration_ids'])) {
      $context['sandbox']['max'] = count($initial_ids);
      $context['sandbox']['migration_ids'] = $initial_ids;
    }
    $migration_id = reset($context['sandbox']['migration_ids']);
    $migration = entity_load('migration', $migration_id);
    // @TODO: if there are no source IDs then remove php.ini time limit.
    $migration->source['database'] = TRUE;
    // @TODO: move time limit back into MigrateExecutable so we can set it here.
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    if ($executable->import() == MigrationInterface::RESULT_COMPLETED) {
      array_shift($context['sandbox']['migration_ids']);
    }
    $context['finished'] = 1 - count($context['sandbox']['migration_ids']) / $context['sandbox']['max'];
  }

}
