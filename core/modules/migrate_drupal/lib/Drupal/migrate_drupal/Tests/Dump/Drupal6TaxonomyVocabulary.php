<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6TaxonomyVocabulary.
 */

namespace Drupal\migrate_drupal\Tests\Dump;
use Drupal\Core\Database\Connection;

/**
 * Database dump for testing taxonomy vocabulary migration.
 */
class Drupal6TaxonomyVocabulary {

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('taxonomy_vocabulary', array(
      'fields' => array(
        'vid' => array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'translatable' => TRUE,
        ),
        'machine_name' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'description' => array(
          'type' => 'text',
          'not null' => FALSE,
          'size' => 'big',
          'translatable' => TRUE,
        ),
        'hierarchy' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'module' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'primary key' => array(
        'vid',
      ),
      'indexes' => array(
        'list' => array(
          'weight',
          'name',
        ),
      ),
      'unique keys' => array(
        'machine_name' => array(
          'machine_name',
        ),
      ),
    ));
    $database->insert('taxonomy_vocabulary')->fields(array(
      'vid',
      'name',
      'machine_name',
      'description',
      'hierarchy',
      'module',
      'weight',
    ))
    ->values(array(
      'vid' => '1',
      'name' => 'Tags',
      'machine_name' => 'tags',
      'description' => 'Use tags to group articles on similar topics into categories.',
      'hierarchy' => '0',
      'module' => 'taxonomy',
      'weight' => '0',
    ))
    ->values(array(
      'vid' => '2',
      'name' => 'Forums',
      'machine_name' => 'forums',
      'description' => 'Forum navigation vocabulary',
      'hierarchy' => '1',
      'module' => 'forum',
      'weight' => '-10',
    ))
    ->values(array(
      'vid' => '5',
      'name' => 'vocabulary 3 (i=2)',
      'machine_name' => 'vocabulary_3_2',
      'description' => 'description of vocabulary 3 (i=2)',
      'hierarchy' => '2',
      'module' => 'taxonomy',
      'weight' => '2',
    ))
    ->execute();
  }

}
