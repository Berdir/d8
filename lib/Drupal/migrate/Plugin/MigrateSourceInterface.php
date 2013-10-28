<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateSourceInterface.
 */

namespace Drupal\migrate\Plugin;

/**
 * Defines an iterface for migrate sources.
 */
interface MigrateSourceInterface extends \Iterator, \Countable {

  /**
   * Returns this source current row primary key representation.
   *
   * @return array
   *   An array representing the primary key of current row.
   */
  public function getCurrentIds();

  /**
   * Returns available fields on the source.
   *
   * @return array
   *   Available fields in the source, keys are the field machine names as used
   *   in field mappings, values are descriptions.
   */
  public function fields();
}
