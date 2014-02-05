<?php

/**
 * @file
 * Contains Drupal\migrate_drupal\Tests\d6\MigrateDrupal6TestBase.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\Core\Database\Database;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

abstract class MigrateDrupal6TestBase extends MigrateDrupalTestBase {

  /**
   * Enables a module on source side.
   *
   * @param string $name
   *   The module to be enabled.
   * @param string $filename
   *   Module file name prefixed with the Drupal system path.
   */
  protected function enableSourceModule($name, $filename) {
    $database = Database::getConnection('default', 'migrate');

    // Assure 'system' table.
    if (!$database->schema()->tableExists('system')) {
      $database->schema()->createTable('system', static::getSystemSchema());
    }

    $info = array(
      'name' => $name,
      'description' => $name,
      'package' => 'Core - optional',
      'version' => '6.31-dev',
      'core' => '6.x',
      'dependencies' => array(),
      'dependents' => array(),
      'php' => '4.3.5',
    );
    $database->merge('system')
      ->key(array('filename' => $filename))
      ->fields(array(
        'name' => $name,
        'type' => 'module',
        'status' => 1,
        'info' => serialize($info),
      ))
      ->execute();
  }

  protected function getSystemSchema() {
    return array(
      'description' => "A list of all modules, themes, and theme engines that are or have been installed in Drupal's file system.",
      'fields' => array(
        'filename' => array(
          'description' => 'The path of the primary file for this item, relative to the Drupal root; e.g. modules/node/node.module.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'name' => array(
          'description' => 'The name of the item; e.g. node.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'type' => array(
          'description' => 'The type of the item, either module, theme, or theme_engine.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'owner' => array(
          'description' => "A theme's 'parent'. Can be either a theme or an engine.",
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'status' => array(
          'description' => 'Boolean indicating whether or not this item is enabled.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
        'throttle' => array(
          'description' => 'Boolean indicating whether this item is disabled when the throttle.module disables throttleable items.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'bootstrap' => array(
          'description' => "Boolean indicating whether this module is loaded during Drupal's early bootstrapping phase (e.g. even before the page cache is consulted).",
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
        'schema_version' => array(
          'description' => "The module's database schema version number. -1 if the module is not installed (its tables do not exist); 0 or the largest N of the module's hook_update_N() function that has either been run or existed when the module was first installed.",
          'type' => 'int',
          'not null' => TRUE,
          'default' => -1,
          'size' => 'small',
        ),
        'weight' => array(
          'description' => "The order in which this module's hooks should be invoked relative to other modules. Equal-weighted modules are ordered by name.",
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
        'info' => array(
          'description' => "A serialized array containing information from the module's .info file; keys can include name, description, package, version, core, dependencies, dependents, and php.",
          'type' => 'text',
          'not null' => FALSE,
        ),
      ),
      'primary key' => array('filename'),
      'indexes' => array(
        'modules' => array(array('type', 12), 'status', 'weight', 'filename'),
        'bootstrap' => array(
          array('type', 12),
          'status',
          'bootstrap',
          'weight',
          'filename',
        ),
        'type_name' => array(array('type', 12), 'name'),
      ),
    );
  }

}
