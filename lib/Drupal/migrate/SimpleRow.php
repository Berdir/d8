<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateRow.
 */

namespace Drupal\migrate;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\Plugin\MigrateRowInterface;

/**
 * This just stores a row without any preparation.
 *
 * Most row classes will extnd this one, overriding prepare().
 */
class SimpleRow implements MigrateRowInterface {

  /**
   * @var array
   */
  protected $source = array();

  protected $keys = array();

  protected $destination = array();

  protected $idMap = array();

  /**
   * Constructs a Migrate>Row object.
   *
   * @param array $values
   *   (optional) An array of values to add as properties on the object.
   */
  public function __construct(array $values = array()) {
    if (empty($values['keys']) || !is_array($values['keys']) || empty($values['data']) || !is_array($values['keys'])) {
      throw new \InvalidArgumentException('A row must have an array of keys.');
    }
    else {
      $this->source = $values['data'];
      foreach ($values['keys'] as $key) {
        if ($this->hasSourceProperty($key)) {
          $this->keys[$key] = $values['data'][$key];
        }
        else {
          throw new InvalidArgumentException("$key has no value");
        }
      }
    }
  }

  public function prepare() {
  }

  public function getSourceKeys() {
    return $this->keys;
  }

  public function hasSourceProperty($property) {
    return isset($this->source[$property]) || array_key_exists($property, $this->source);
  }

  public function getSourceProperty($property) {
    if ($this->hasSourceProperty($property)) {
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

  public function getIddMapProperty($property) {
    if ($this->hasIdMapProperty($property)) {
      return $this->idMap[$property];
    }
  }

  /**
   * @return bool
   */
  public function hasIdMapProperty($property) {
    return isset($this->idMap[$property]) || array_key_exists($property, $this->idMap);
  }
}
