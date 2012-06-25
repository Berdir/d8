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

  public function getPropertyDefinition($name);

  public function getPropertyDefinitions();

/*
 * Commented out for now as it creates problems for entities.
 *
 *   public function __get($name);

   public function __set($name, $value);*/
}
