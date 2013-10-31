<?php

use Drupal\migrate\DrupalMessage;

$values = array(
  'id' => 'first',
  'source' => array(
    'plugin' => 'drupal6_variable',
    'variables' => array(
      'site_mail',
      'site_name',
    ),
  ),
  'process' => array(

  ),
  'destination' => array(
    'plugin' => 'd8_config',
    'config_name' => 'migrate.test',
  ),
);

$migration = entity_create('migration', $values);
$executable = new \Drupal\migrate\MigrateExecutable($migration, new DrupalMessage);
$executable->import();
