<?php

/**
 * @file
 * Definition of Drupal\Core\Property\PropertyContainerInterface.
 */

namespace Drupal\Core\Property;
use IteratorAggregate;

/**
 * Interface for property containers.
 *
 * This is implemented by entities as well as by PropertyItem classes.
 */
interface PropertyContainerInterface extends IteratorAggregate  {

  // Check item access.
  public function access($account);

  // Validate the item value.
  public function validate();

  public function getProperties();

  public function getPropertyDefinitions();

  public function get($name);

  /**
   * Gets the raw value, i.e. the id of the entity in case of entity references,
   * or the plain data of fields.
   */
  public function getRawValue($property_name);

  public function set($name, $value);

  public function __get($name);

  public function __set($name, $value);
}
