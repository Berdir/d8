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

  protected $frozen = FALSE;

  /**
   * Constructs a Migrate>Row object.
   *
   * @param array $source_ids
   *   An array containing the ids of the source using the keys as the field
   *   names.
   * @param array $values
   *   An array of values to add as properties on the object.
   *
   * @throws \InvalidArgumentException
   *   Thrown when a source id property does not exist.
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

  /**
   * Retrieves the values of the source identifiers.
   *
   * @return array
   *   An array containing the values of the source identifiers.
   */
  public function getSourceIdValues() {
    return $this->sourceIdValues;
  }

  /**
   * Determines whether a source has a property.
   *
   * @param string $property
   *   A property on the source.
   *
   * @return bool
   *   TRUE if the source has property; FALSE otherwise.
   */
  public function hasSourceProperty($property) {
    return isset($this->source[$property]) || array_key_exists($property, $this->source);
  }

  /**
   * Retrieves a source property.
   *
   * @param string $property
   *   A property on the source.
   *
   * @return mixed|null
   *   The found returned property or NULL if not found.
   */
  public function getSourceProperty($property) {
    if (isset($this->source[$property])) {
      return $this->source[$property];
    }
  }

  /**
   * This returns the whole source array.
   *
   * @return array
   *   An array of source plugins.
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * Sets a source property. This can only be called from the source plugin.
   *
   * @param string $property
   *   A property on the source.
   * @param mixed $data
   *   The property value to set on the source.
   *
   * @throws \Exception
   */
  public function setSourceProperty($property, $data) {
    if ($this->frozen) {
      throw new \Exception("The source is frozen and can't be changed any more");
    }
    else {
      $this->source[$property] = $data;
    }
  }

  /**
   * Freezes the source.
   */
  public function freezeSource() {
    $this->frozen = TRUE;
  }

  /**
   * Determines whether a destination has a property.
   *
   * @param string $property
   *   A property on the destination.
   *
   * @return bool
   *   TRUE if the destination has property; FALSE otherwise.
   */
  public function hasDestinationProperty($property) {
    return isset($this->destination[$property]) || array_key_exists($property, $this->destination);
  }

  /**
   * Sets a destination property.
   *
   * @param string $property
   *   A property on the destination.
   * @param mixed $value
   *   The property value to set on the destination.
   */
  public function setDestinationProperty($property, $value) {
    $this->destination[$property] = $value;
  }

  /**
   * Sets destination properties.
   *
   * @param array $property_keys
   *   An array of properties on the destination.
   * @param mixed $value
   *   The property value to set on the destination.
   */
  public function setDestinationPropertyDeep(array $property_keys, $value) {
    NestedArray::setValue($this->destination, $property_keys, $value, TRUE);
  }

  /**
   * This returns the whole destination array.
   *
   * @return array
   *   An array of destination plugins.
   */
  public function getDestination() {
    return $this->destination;
  }

  /**
   * Sets the Migrate id mappings.
   *
   * @param array $id_map
   *   An array of mappings between source ID and destination ID.
   */
  public function setIdMap(array $id_map) {
    $this->idMap = $id_map;
  }

  /**
   * Retrieves the Migrate id mappings.
   *
   * @return array
   *   An array of id mappings between source and destination identifiers.
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
   * Returns if this row needs an update.
   *
   * @return bool
   *   TRUE if the row needs updating, FALSE otherwise.
   */
  public function needsUpdate() {
    return $this->idMap['needs_update'] == MigrateIdMapInterface::STATUS_NEEDS_UPDATE;
  }

  /**
   * Returns the hash for the source values..
   *
   * @return mixed
   *   The hash of the source values.
   */
  public function getHash() {
    return $this->idMap['hash'];
  }
}
