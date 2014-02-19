<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6DumpBase.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

class Drupal6DumpCommon {

  public static function createVariable(Connection $database) {
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
      'module' => 'book',
      'name' => 'variable',
    ));
  }

  /**
   * Create the system table if it doesn't already exist.
   *
   * @param Connection $database
   *   The connection object.
   */
  public static function createSystem(Connection $database) {
    if (!$database->schema()->tableExists('system')) {
      $database->schema()->createTable('system', array(
        'description' => "A list of all modules, themes, and theme engines that are or have been installed in Drupal's file system.",
        'fields' => array(
          'filename' => array(
            'description' => 'The path of the primary file for this item, relative to the Drupal root; e.g. modules/node/node.module.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'name' => array(
            'description' => 'The name of the item; e.g. node.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'type' => array(
            'description' => 'The type of the item, either module, theme, or theme_engine.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'owner' => array(
            'description' => "A theme's 'parent'. Can be either a theme or an engine.",
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'status' => array(
            'description' => 'Boolean indicating whether or not this item is enabled.',
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0),
          'throttle' => array(
            'description' => 'Boolean indicating whether this item is disabled when the throttle.module disables throttleable items.',
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0,
            'size' => 'tiny'),
          'bootstrap' => array(
            'description' => "Boolean indicating whether this module is loaded during Drupal's early bootstrapping phase (e.g. even before the page cache is consulted).",
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0),
          'schema_version' => array(
            'description' => "The module's database schema version number. -1 if the module is not installed (its tables do not exist); 0 or the largest N of the module's hook_update_N() function that has either been run
   or existed when the module was first installed.",
            'type' => 'int',
            'not null' => TRUE,
            'default' => -1,
            'size' => 'small'),
          'weight' => array(
            'description' => "The order in which this module's hooks should be invoked relative to other modules. Equal-weighted modules are ordered by name.",
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0),
          'info' => array(
            'description' => "A serialized array containing information from the module's .info file; keys can include name, description, package, version, core, dependencies, dependents, and php.",
            'type' => 'text',
            'not null' => FALSE,
          )),
        'primary key' => array('filename'),
        'indexes' => array(
          'modules' => array(array('type', 12), 'status', 'weight', 'filename'),
          'bootstrap' => array(array('type', 12), 'status', 'bootstrap', 'weight', 'filename'),
          'type_name' => array(array('type', 12), 'name'),
        ),
      ));
    }
  }

  public static function setModuleVersion(Connection $database, $module, $version, $status = 1) {
    $database->merge('system')
      ->key(array('filename' => "modules/$module"))
      ->fields(array(
        'type' => 'module',
        'name' => $module,
        'schema_version' => $version,
        'status' => $status,
      ))
      ->execute();
  }
}
