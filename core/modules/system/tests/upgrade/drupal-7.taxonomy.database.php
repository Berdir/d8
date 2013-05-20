<?php

/**
 * @file
 * Database additions for taxonomy tests. Used in
 * \Drupal\system\Tests\Upgrade\TaxonomyUpgradePathTest.
 *
 * This dump only contains data and schema components relevant for taxonomy
 * functionality. The drupal-7.standard-all.database.php file is imported
 * before this dump, so the two form the database structure expected in tests
 * altogether.
 */

db_insert('taxonomy_term_data')
  ->fields(array('tid', 'vid', 'name', 'description', 'format', 'weight'))
  ->values(array(
    'tid' => 5,
    'vid' => 1,
    'name' => 'A tag',
    'description' => 'Description of a tag',
    'format' => 'plain_text',
    'weight' => 10,
  ))
  ->values(array(
    'tid' => 6,
    'vid' => 1,
    'name' => 'Another tag',
    'description' => '<strong>HTML</strong> Description',
    'format' => 'filtered_html',
    'weight' => 20,
  ))
  ->execute();
