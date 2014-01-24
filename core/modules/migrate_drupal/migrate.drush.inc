<?php

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Symfony\Component\Yaml\Yaml;

function migrate_drush_command() {
  $items['migrate-manifest'] = array(
    'arguments' => array(
      'db_url' => 'The source database URL',
      'manifest' => 'The name of the manifest file',
    ),
    'options' => array(
      'keep' => 'To keep the id map of the previous migration. Omit to completely rerun migrations.',
    ),
  );
  return $items;
}

function drush_migrate_manifest($db_url, $manifest) {
  require_once __DIR__ . '/drushmigratemessage.inc';
  $db_spec = drush_convert_db_from_db_url($db_url);
  Database::addConnectionInfo('migrate', 'default', $db_spec);
  $list = Yaml::parse($manifest);
  $message = new DrushMigrateMessage();
  $keep = drush_get_option('keep');
  foreach (entity_load_multiple('migration', $list) as $migration_id => $migration) {
    drush_log("Running $migration_id");
    if (!$keep) {
      db_truncate($migration->getIdMap()->getMapTableName())->execute();
    }
    $executable = new MigrateExecutable($migration, $message);
    $executable->import();
  }
  include_once DRUPAL_ROOT . '/core/includes/utility.inc';
  drupal_rebuild();
}
