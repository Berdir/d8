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

  /**
   * Validate the container values.
   */
  public function validate();

  /**
   * Gets an array of all properties.
   *
   * @return array
   *   An array of properties, keyed by property name.
   */
  public function getProperties();

  /**
   * Gets the definition of a contained property.
   *
   * @param string $name
   *   The name of property.
   *
   * @return array
   *   The definition of the property.
   */
  public function getPropertyDefinition($name);

  /**
   * Gets an array of all definitions of contained properties.
   *
   * @return array
   *   An array of property definitions.
   */
  public function getPropertyDefinitions();

/*
 * Commented out for now as it creates problems for entities.
 *
 *   public function __get($name);

   public function __set($name, $value);*/
}
