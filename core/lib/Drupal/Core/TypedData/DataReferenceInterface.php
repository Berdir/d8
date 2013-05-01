<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\DataReferenceInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for typed data references.
 */
interface DataReferenceInterface  {

  /**
   * Gets the data definition of the referenced data.
   *
   * @return array
   *   The data definition of the referenced data.
   */
  public function getTargetDefinition();

  /**
   * Gets the referenced data.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The referenced typed data object.
   */
  public function getTarget();

  /**
   * Gets the identifier of the referenced data.
   *
   * @return mixed
   *   The identifier of the referenced data.
   */
  public function getTargetIdentifier();
}
