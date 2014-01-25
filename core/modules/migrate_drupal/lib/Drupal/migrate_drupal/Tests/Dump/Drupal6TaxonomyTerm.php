<?php
/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6TaxonomyTerm.
 */

namespace Drupal\migrate_drupal\Tests\Dump;
use Drupal\Core\Database\Connection;

/**
 * Database dump for testing taxonomy term migration.
 */
class Drupal6TaxonomyTerm {

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('vocabulary', array(
      'description' => 'Stores vocabulary information.',
      'fields' => array(
        'vid' => array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'Primary Key: Unique vocabulary ID.',
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'description' => 'Name of the vocabulary.',
        ),
        'description' => array(
          'type' => 'text',
          'not null' => FALSE,
          'size' => 'big',
          'description' => 'Description of the vocabulary.',
        ),
        'help' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'description' => 'Help text to display for the vocabulary.',
        ),
        'relations' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'Whether or not related terms are enabled within the vocabulary. (0 = disabled, 1 = enabled)',
        ),
        'hierarchy' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'The type of hierarchy allowed within the vocabulary. (0 = disabled, 1 = single, 2 = multiple)',
        ),
        'multiple' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'Whether or not multiple terms from this vocabulary may be assigned to a node. (0 = disabled, 1 = enabled)',
        ),
        'required' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'Whether or not terms are required for nodes using this vocabulary. (0 = disabled, 1 = enabled)',
        ),
        'tags' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'Whether or not free tagging is enabled for the vocabulary. (0 = disabled, 1 = enabled)',
        ),
        'module' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The module which created the vocabulary.',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'The weight of the vocabulary in relation to other vocabularies.',
        ),
      ),
      'primary key' => array('vid'),
      'indexes' => array(
        'list' => array('weight', 'name'),
      ),
    ));

    $database->schema()->createTable('term_data', array(
      'fields' => array(
        'tid' => array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'vid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'description' => array(
          'type' => 'text',
          'not null' => FALSE,
          'size' => 'big',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
      ),
      'primary key' => array(
        'tid',
      ),
      'indexes' => array(
        'taxonomy_tree' => array(
          'vid',
          'weight',
          'name',
        ),
        'vid_name' => array(
          'vid',
          'name',
        ),
      ),
      'module' => 'taxonomy',
      'name' => 'term_data',
    ));

    $database->schema()->createTable('term_hierarchy', array(
      'fields' => array(
        'tid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'parent' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'indexes' => array(
        'parent' => array(
          'parent',
        ),
      ),
      'primary key' => array(
        'tid',
        'parent',
      ),
      'module' => 'taxonomy',
      'name' => 'term_hierarchy',
    ));

    $database->insert('vocabulary')->fields(array(
      'vid',
      'name',
      'description',
      'help',
      'relations',
      'hierarchy',
      'multiple',
      'required',
      'tags',
      'module',
      'weight',
    ))
      ->values(array(
        'vid' => '1',
        'name' => 'vocabulary 1 (i=0)',
        'description' => 'description of vocabulary 1 (i=0)',
        'help' => '',
        'relations' => '1',
        'hierarchy' => '0',
        'multiple' => '0',
        'required' => '0',
        'tags' => '0',
        'module' => 'taxonomy',
        'weight' => '4',
      ))
      ->values(array(
        'vid' => '2',
        'name' => 'vocabulary 2 (i=1)',
        'description' => 'description of vocabulary 2 (i=1)',
        'help' => '',
        'relations' => '1',
        'hierarchy' => '1',
        'multiple' => '1',
        'required' => '0',
        'tags' => '0',
        'module' => 'taxonomy',
        'weight' => '5',
      ))
      ->values(array(
        'vid' => '3',
        'name' => 'vocabulary 3 (i=2)',
        'description' => 'description of vocabulary 3 (i=2)',
        'help' => '',
        'relations' => '1',
        'hierarchy' => '2',
        'multiple' => '0',
        'required' => '0',
        'tags' => '0',
        'module' => 'taxonomy',
        'weight' => '6',
      ))
      ->execute();

    $database->insert('term_data')->fields(array(
      'tid',
      'vid',
      'name',
      'description',
      'weight',
    ))
      ->values(array(
        'tid' => '1',
        'vid' => '1',
        'name' => 'term 1 of vocabulary 1',
        'description' => 'description of term 1 of vocabulary 1',
        'weight' => '0',
      ))
      ->values(array(
        'tid' => '2',
        'vid' => '2',
        'name' => 'term 2 of vocabulary 2',
        'description' => 'description of term 2 of vocabulary 2',
        'weight' => '3',
      ))
      ->values(array(
        'tid' => '3',
        'vid' => '2',
        'name' => 'term 3 of vocabulary 2',
        'description' => 'description of term 3 of vocabulary 2',
        'weight' => '4',
      ))
      ->values(array(
        'tid' => '4',
        'vid' => '3',
        'name' => 'term 4 of vocabulary 3',
        'description' => 'description of term 4 of vocabulary 3',
        'weight' => '6',
      ))
      ->values(array(
        'tid' => '5',
        'vid' => '3',
        'name' => 'term 5 of vocabulary 3',
        'description' => 'description of term 5 of vocabulary 3',
        'weight' => '7',
      ))
      ->values(array(
        'tid' => '6',
        'vid' => '3',
        'name' => 'term 6 of vocabulary 3',
        'description' => 'description of term 6 of vocabulary 3',
        'weight' => '8',
      ))
      ->execute();

    $database->insert('term_hierarchy')->fields(array(
      'tid',
      'parent',
    ))
      ->values(array(
        'tid' => '1',
        'parent' => '0',
      ))
      ->values(array(
        'tid' => '2',
        'parent' => '0',
      ))
      ->values(array(
        'tid' => '4',
        'parent' => '0',
      ))
      ->values(array(
        'tid' => '3',
        'parent' => '2',
      ))
      ->values(array(
        'tid' => '5',
        'parent' => '4',
      ))
      ->values(array(
        'tid' => '6',
        'parent' => '4',
      ))
      ->values(array(
        'tid' => '6',
        'parent' => '5',
      ))
      ->execute();
  }

}
