<?php

use Drupal\migrate\DrupalMessage;
use Drupal\migrate\MigrateExecutable;

$values = array(
  'id' => 'first',
  'source' => array(
    'plugin' => 'drupal6_variable',
    'variables' => array(
      'site_name',
      'site_mail',
      'site_403',
      'site_404',
    ),
  ),
  'process' => array(
    array(
      'source' => 'site_name',
      'destination' => 'name',
    ),
    array(
      'source' => 'site_mail',
      'destination' => 'mail',
    ),
    array(
      'source' => 'site_403',
      'destination' => 'page:403',
    ),
    array(
      'source' => 'site_404',
      'destination' => 'page:404',
    )
  ),
  'destination' => array(
    'plugin' => 'd8_config',
    'config_name' => 'migrate.test',
  ),
);

$migration = entity_create('migration', $values);
$executable = new MigrateExecutable($migration, new DrupalMessage);
$executable->import();
