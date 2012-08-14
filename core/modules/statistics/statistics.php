<?php

/**
 * @file
 * Handles counts of node views via AJAX with minimal bootstrap.
 */

// Change the directory to the Drupal root.
chdir('../../..');

/**
* Root directory of Drupal installation.
*/
define('DRUPAL_ROOT', getcwd());

include_once DRUPAL_ROOT . '/core/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

if (config('statistics.settings')->get('count_content_views')) {
  $nid = filter_input(INPUT_POST, 'nid', FILTER_VALIDATE_INT);
  if ($nid) {
    db_merge('node_counter')
      ->key(array('nid' => $nid))
      ->fields(array(
        'daycount' => 1,
        'totalcount' => 1,
        'timestamp' => REQUEST_TIME,
      ))
      ->expression('daycount', 'daycount + 1')
      ->expression('totalcount', 'totalcount + 1')
      ->execute();
  }
}

