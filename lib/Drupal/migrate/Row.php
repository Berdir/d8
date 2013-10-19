<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateRow.
 */

namespace Drupal\migrate;

use Drupal\Component\Utility\NestedArray;

/**
 * This just stores a row.
 */
class Row {

  /**
   * @var array
   */
  protected $source = array();

  protected $keys = array();

  protected $destination = array();

  protected $idMap = array(
    'original_hash' => '',
    'hash' => '',
  );

  /**
   * Constructs a Migrate>Row object.
   *
   * @param array $values
   *   (optional) An array of values to add as properties on the object.
   */
  public function __construct(array $keys, array $values) {
    if (empty($values['keys'])) {
      throw new \InvalidArgumentException('A row must have an array of keys.');
    }
    else {
      $this->source = $values['data'];
      foreach ($values['keys'] as $key) {
        if ($this->hasSourceProperty($key)) {
          $this->keys[$key] = $values['data'][$key];
        }
        else {
          throw new \InvalidArgumentException("$key has no value");
        }
      }
    }
  }

  public function getSourceKeys() {
    return $this->keys;
  }

  public function hasSourceProperty($property) {
    return isset($this->source[$property]) || array_key_exists($property, $this->source);
  }

  public function getSourceProperty($property) {
    if (isset($this->source[$property])) {
      return $this->source[$property];
    }
  }


  public function hasDestinationProperty($property) {
    return isset($this->destination[$property]) || array_key_exists($property, $this->destination);
  }

  public function setDestinationProperty($property, $value) {
    $this->destination[$property] = $value;
  }

  public function setDestinationPropertyDeep(array $property_keys, $value) {
    NestedArray::setValue($this->destination, $property_keys, $value, TRUE);
  }

  public function getDestination() {
    return $this->destination;
  }

  public function setIdMap(array $id_map) {
    $this->idMap = $id_map;
  }

  /**
   * @return array
   */
  public function getIdMap() {
    return $this->idMap;
  }

  /**
   * Recalculate the hash for the row.
   */
  public function rehash() {
    $this->idMap['original_hash'] = $this->idMap['hash'];
    $this->idMap['hash'] = hash('sha256', serialize($this->source));
  }

  /**
   * Checks whether the row has changed compared to the original id map.
   *
   * return bool
   *   TRUE if the row has changed, FALSE otherwise. If setIdMap() was not
   *   called, this always returns FALSE.
   */
  public function changed() {
    return $this->idMap['original_hash'] != $this->idMap['hash'];
  }

  /**
   * @return bool
   *   TRUE if the row needs updating.
   */
  public function needsUpdate() {
    return $this->idMap['needs_update'] == \MigrateMap::STATUS_NEEDS_UPDATE;
  }

}
