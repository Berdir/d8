<?php

/**
 * @file
 * Database additions for system tests. Used in upgrade.system.test.
 *
 * This dump only contains data and schema components relevant for system
 * functionality. The drupal-7.filled.bare.php file is imported before
 * this dump, so the two form the database structure expected in tests
 * altogether.
 */

// Add non-default system settings.
db_insert('variable')->fields(array(
  'name',
  'value',
))
->values(array(
  'name' => 'cache',
  'value'=> 'i:1;',
))
->values(array(
    'name' => 'cache_lifetime',
    'value' => 's:5:"10800";',
  ))
->values(array(
    'name' => 'page_cache_maximum_age',
    'value' => 's:4:"1800";',
  ))
->values(array(
    'name' => 'page_compression',
    'value' => 'i:1;',
  ))
->values(array(
    'name' => 'preprocess_css',
    'value' => 'i:1;',
  ))
->values(array(
    'name' => 'preprocess_js',
    'value' => 'i:1;',
  ))
->values(array(
    'name' => 'cron_safe_threshold',
    'value' => 's:5:"86400";',
  ))
->values(array(
    'name' => 'cron_threshold_warning',
    'value' => 's:5:"86400";',
  ))
->values(array(
    'name' => 'cron_threshold_error',
    'value' => 's:6:"172800";',
  ))
->values(array(
    'name' => 'error_level',
    'value' => 's:1:"1";',
  ))
->values(array(
    'name' => 'maintenance_mode',
    'value' => 'i:1;',
  ))
->values(array(
    'name' => 'maintenance_mode_message',
    'value' => 's:22:"Testing config upgrade"',
  ))
->values(array(
    'name' => 'feed_default_items',
    'value' => 's:2:"20";',
  ))
->values(array(
    'name' => 'feed_description',
    'value' => 's:22:"Testing config upgrade";',
  ))
->values(array(
    'name' => 'feed_item_length',
    'value' => 's:6:"teaser";',
  ))
->values(array(
    'name' => 'site_403',
    'value' => 's:3:"403";',
  ))
->values(array(
    'name' => 'site_404',
    'value' => 's:3:"404";',
  ))
->values(array(
    'name' => 'site_frontpage',
    'value' => 's:4:"node";',
  ))
->values(array(
    'name' => 'site_slogan',
    'value' => 's:31:"CMI makes Drupal 8 drush cex -y";',
  ))
->values(array(
  'name' => 'user_cancel_method',
  'value' => 's:20:"user_cancel_reassign"',
  ))
->values(array(
    'name' => 'taxonomy_override_selector',
    'value' => 'i:1;',
  ))
->values(array(
    'name' => 'taxonomy_terms_per_page_admin',
    'value' => 'i:32;',
  ))
->values(array(
    'name' => 'taxonomy_maintain_index_table',
    'value' => 'i:0;',
 ))
->values(array(
  'name' => 'filter_allowed_protocols',
  'value' => 'a:4:{i:0;s:4:"http";i:1;s:5:"https";i:2;s:3:"ftp";i:3;s:6:"mailto";}',
  ))
->values(array(
    'name' => 'password_count_log2',
    'value' => 'i:42;',
))
->values(array(
    'name' => 'actions_max_stack',
    'value' => 'i:42;',
  ))
->execute();

db_update('variable')
  ->fields(array('value' => 's:18:"config@example.com";'))
  ->condition('name', 'site_mail')
  ->execute();
db_update('variable')
    ->fields(array('value' => 's:22:"Testing config upgrade";'))
    ->condition('name', 'site_name')
    ->execute();
db_update('variable')
  ->fields(array('value' => 's:10:"plain_text";'))
  ->condition('name', 'filter_fallback_format')
  ->execute();
