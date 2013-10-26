<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateRow.
 */

namespace Drupal\migrate;

use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\Plugin\MigrateIdMapInterface;

/**
 * This just stores a row.
 */
class Row {

  /**
   * @var array
   */
  protected $source = array();

  /**
   * The value of the source identifiers.
   *
   * This is a subset of the $source array.
   *
   * @var array
   */
  protected $sourceIdValues = array();

  /**
   * The destination values.
   *
   * @var array
   */
  protected $destination = array();

  protected $idMap = array(
    'original_hash' => '',
    'hash' => '',
    'needs_update' => MigrateIdMapInterface::STATUS_NEEDS_UPDATE,
  );

  /**
   * Constructs a Migrate>Row object.
   *
   * @param array $source_ids
   *   An array containing the ids of the source using the keys as the field
   *   names.
   * @param array $values
   *   An array of values to add as properties on the object.
   */
  public function __construct(array $source_ids, array $values) {
    $this->source = $values;
    foreach (array_keys($source_ids) as $id) {
      if ($this->hasSourceProperty($id)) {
        $this->sourceIdValues[$id] = $values[$id];
      }
      else {
        throw new \InvalidArgumentException("$id has no value");
      }
    }
  }

  public function getSourceIdValues() {
    return $this->sourceIdValues;
  }

  public function hasSourceProperty($property) {
    return isset($this->source[$property]) || array_key_exists($property, $this->source);
  }

  public function getSourceProperty($property) {
    if (isset($this->source[$property])) {
      return $this->source[$property];
    }
  }

  /**
   * This returns the whole source array. There is no setter; the source is
   * immutable.
   *
   * @return array
   */
  public function getSource() {
    return $this->source;
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
    return $this->idMap['needs_update'] == MigrateIdMapInterface::STATUS_NEEDS_UPDATE;
  }

  public function getHash() {
    return $this->idMap['hash'];
  }
}
