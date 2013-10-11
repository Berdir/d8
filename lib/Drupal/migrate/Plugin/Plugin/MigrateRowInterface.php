<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateRowInterface.
 */

namespace Drupal\migrate\Plugin;

interface MigrateRowInterface {

  public function prepare();

  public function hasSourceProperty($property);
  public function getSourceProperty($property);
  public function getSource();

  public function hasDestinationProperty($property);
  public function setDestinationProperty($property, $value);
  public function setDestinationPropertyDeep(array $property_keys, $value);
  public function getDestination();

}
