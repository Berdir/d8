<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * A base source class for Drupal 6 migrate sources.
 *
 * Mainly to let children retrieve information from the origin system in an
 * easier way.
 */
abstract class Drupal6SqlBase extends SqlBase {

  /**
   * Get a module schema_version value in the source installation.
   *
   * @param string $module
   *   Name of module.
   *
   * @return int
   *   The current module schema version on the origin system table.
   */
  protected function getModuleSchemaVersion($module) {
    $schema_version= $this->database
      ->select('system', 's')
      ->fields('s', array('schema_version'))
      ->condition('name', $module)
      ->condition('type', 'module')
      ->execute()
      ->fetchField();
    return $schema_version;
  }

  /**
   * Check to see if a given module is enabled in the source installation.
   *
   * @param string $module
   *   Name of module to check.
   *
   * @return boolean
   *   TRUE if module is enabled on the origin system, FALSE if not.
   */
  protected function moduleExists($module) {
    $exists = $this->database
      ->select('system', 's')
      ->fields('s', array('status'))
      ->condition('name', $module)
      ->condition('type', 'module')
      ->execute()
      ->fetchField();
    return (bool) $exists;
  }

}
