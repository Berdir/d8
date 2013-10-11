<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateRowInterface.
 */

namespace Drupal\migrate\Plugin;

interface MigrateRowInterface {

  public function prepare();

  /**
   * @return bool
   */
  public function hasSourceProperty($property);
  public function getSourceProperty($property);

  /**
   * @@TODO: I do not think we need to support retrieving the whole thing at once.
   */
  //public function getSource();

  /**
   * Retrieves the value(s) for the current key.
   *
   * The first call should do any processing necessary for the key values.
   *
   * @return array
   */
  public function getSourceKeys();

  public function setIdMap(array $id_map);
  public function getIddMapProperty($property);

  /**
   * @return bool
   */
  public function hasIdMapProperty($property);

  /**
   * @return bool
   */
  public function hasDestinationProperty($property);
  public function setDestinationProperty($property, $value);
  public function setDestinationPropertyDeep(array $property_keys, $value);

  /**
   * @return array
   */
  public function getDestination();



}
