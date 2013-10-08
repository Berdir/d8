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
    $this->source = new \stdClass;
    foreach ($values as $key => $value) {
      $this->{$key} = $value;
    }
    $this->destination = new \stdClass;
  }

  public function set(array $keys, $value) {
    $ref = &$this->destination;
    foreach ($keys as $key) {
      if (!property_exists($ref, $key)) {
        $ref->$key = new \stdClass;
      }
      $ref = &$ref->$key;
    }
    $ref = $value;
  }
}
