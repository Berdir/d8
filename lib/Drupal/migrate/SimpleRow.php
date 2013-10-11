<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateRow.
 */

namespace Drupal\migrate;

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

  protected $destination = array();

  /**
   * Constructs a Migrate>Row object.
   *
   * @param array $values
   *   (optional) An array of values to add as properties on the object.
   */
  public function __construct(array $values = array()) {
    $this->source = $values;
  }

  public function prepare() {
  }

  public function hasSourceProperty($property) {
    return isset($this->source[$property]) || array_key_exists($property, $this->source);
  }

  public function getSourceProperty($property) {
    if ($this->hasSourceProperty($property)) {
      return $this->source[$property];
    }
  }

  public function getSource() {
    return $this->getSource();
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
}
