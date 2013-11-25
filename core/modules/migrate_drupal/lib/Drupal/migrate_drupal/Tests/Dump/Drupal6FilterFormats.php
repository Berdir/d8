<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Dump\Drupal6FilterFormats.
 */

namespace Drupal\migrate_drupal\Tests\Dump;
use Drupal\Core\Database\Connection;

/**
 * Database dump for testing filter format migration.
 */
class Drupal6FilterFormats {


  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('filters', array(
      'description' => 'Table that maps filters (HTML corrector) to input formats (Filtered HTML).',
      'fields' => array(
        'fid' => array(
          'type' => 'serial',
          'not null' => TRUE,
          'description' => 'Primary Key: Auto-incrementing filter ID.',
        ),
        'format' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Foreign key: The {filter_formats}.format to which this filter is assigned.',
        ),
        'module' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The origin module of the filter.',
        ),
        'delta' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'ID to identify which filter within module is being referenced.',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'Weight of filter within format.',
        )
      ),
      'primary key' => array('fid'),
      'unique keys' => array(
        'fmd' => array('format', 'module', 'delta'),
      ),
      'indexes' => array(
        'list' => array('format', 'weight', 'module', 'delta'),
      ),
    ));
    $database->schema()->createTable('filter_formats', array(
      'description' => 'Stores input formats: custom groupings of filters, such as Filtered HTML.',
      'fields' => array(
        'format' => array(
          'type' => 'serial',
          'not null' => TRUE,
          'description' => 'Primary Key: Unique ID for format.',
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'description' => 'Name of the input format (Filtered HTML).',
        ),
        'roles' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'description' => 'A comma-separated string of roles; references {role}.rid.', // This is bad since you can't use joins, nor index.
        ),
        'cache' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'Flag to indicate whether format is cachable. (1 = cachable, 0 = not cachable)',
        ),
      ),
      'primary key' => array('format'),
      'unique keys' => array('name' => array('name')),
    ));
    $database->schema()->createTable('variable', array(
      'fields' => array(
        'name' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
        ),
        'value' => array(
          'type' => 'blob',
          'not null' => TRUE,
          'size' => 'big',
          'translatable' => TRUE,
        ),
      ),
      'primary key' => array(
        'name',
      ),
      'module' => 'filter',
      'name' => 'variable',
    ));
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'allowed_html_1',
      'value' => 's:61:"<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd>";',
    ))
    ->values(array(
      'name' => 'filter_html_help_1',
      'value' => 'i:1;',
    ))
    ->values(array(
      'name' => 'filter_html_nofollow_1',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'filter_url_length_1',
      'value' => 's:2:"72";',
    ))
    ->execute();
    $database->insert('filter_formats')->fields(array(
      'format',
      'name',
      'roles',
      'cache',
    ))
    ->values(array(
      'format' => '1',
      'name' => 'Filtered HTML',
      'roles' => ',1,2,',
      'cache' => '1',
    ))
    ->values(array(
      'format' => '2',
      'name' => 'Full HTML',
      'roles' => '',
      'cache' => '1',
    ))
    ->values(array(
      'format' => '3',
      'name' => 'Escape HTML Filter',
      'roles' => '',
      'cache' => '1',
    ))
    ->execute();

    $database->insert('filters')->fields(array(
      'fid',
      'format',
      'module',
      'delta',
      'weight',
    ))
    ->values(array(
      'fid' => '1',
      'format' => '1',
      'module' => 'filter',
      'delta' => '2',
      'weight' => '0',
    ))
    ->values(array(
      'fid' => '2',
      'format' => '1',
      'module' => 'filter',
      'delta' => '0',
      'weight' => '1',
    ))
    ->values(array(
      'fid' => '3',
      'format' => '1',
      'module' => 'filter',
      'delta' => '1',
      'weight' => '2',
    ))
    ->values(array(
      'fid' => '4',
      'format' => '1',
      'module' => 'filter',
      'delta' => '3',
      'weight' => '10',
    ))
    ->values(array(
      'fid' => '5',
      'format' => '2',
      'module' => 'filter',
      'delta' => '2',
      'weight' => '0',
    ))
    ->values(array(
      'fid' => '6',
      'format' => '2',
      'module' => 'filter',
      'delta' => '1',
      'weight' => '1',
    ))
    ->values(array(
      'fid' => '7',
      'format' => '2',
      'module' => 'filter',
      'delta' => '3',
      'weight' => '10',
    ))
    ->execute();

  }
}
