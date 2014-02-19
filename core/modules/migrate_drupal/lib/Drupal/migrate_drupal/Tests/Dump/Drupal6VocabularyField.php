<?php
/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6VocabularyField.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing vocabulary to field migration.
 */
class Drupal6VocabularyField {

  /**
   * {@inheritdoc}
   */
  public static function load(Connection $database) {

    $database->schema()->createTable('vocabulary', array(
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
        ),
        'description' => array(
          'type' => 'text',
          'not null' => FALSE,
          'size' => 'big',
        ),
        'help' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'relations' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'hierarchy' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'multiple' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'required' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'tags' => array(
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
          'size' => 'tiny',
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
      'module' => 'taxonomy',
      'name' => 'vocabulary',
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
      'name' => 'Tags',
      'description' => 'Tags Vocabulary',
      'help' => '',
      'relations' => '1',
      'hierarchy' => '0',
      'multiple' => '0',
      'required' => '0',
      'tags' => '0',
      'module' => 'taxonomy',
      'weight' => '0',
    ))->execute();


    $database->schema()->createTable('vocabulary_node_types', array(
      'description' => 'Stores which node types vocabularies may be used with.',
      'fields' => array(
        'vid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Primary Key: the {vocabulary}.vid of the vocabulary.',
        ),
        'type' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The {node}.type of the node type for which the vocabulary may be used.',
        ),
      ),
      'primary key' => array('type', 'vid'),
      'indexes' => array(
        'vid' => array('vid'),
      ),
    ));

    $database->insert('vocabulary_node_types')->fields(array(
      'vid',
      'type',
    ))
    ->values(array(
      'vid' => '1',
      'type' => 'article',
    ))
    ->values(array(
      'vid' => '1',
      'type' => 'page',
    ))
    ->execute();
    Drupal6DumpCommon::createSystem($database);
    Drupal6DumpCommon::setModuleVersion($database, 'taxonomy', 6001);
  }

}
