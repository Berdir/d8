<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateRow.
 */

namespace Drupal\migrate;

class MigrateRow {

  /**
   * @var \stdClass
   */
  public $source;

  /**
   * Constructs a Migrate>Row object.
   *
   * @param array $values
   *   (optional) An array of values to add as properties on the object.
   */
  public function __construct(array $values = array()) {
    foreach ($values as $key => $value) {
      $this->{$key} = $value;
    }
  }

}
