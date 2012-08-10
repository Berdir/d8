<?php

/**
 * @file
 * Definition of Drupal\Core\Property\PropertyListInterface.
 */

namespace Drupal\Core\Property;
use IteratorAggregate;
use ArrayAccess;
use Countable;

/**
 * Interface for a list of properties.
 */
interface PropertyListInterface extends PropertyInterface, ArrayAccess, IteratorAggregate, Countable { }