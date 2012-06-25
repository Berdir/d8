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
 * Interface for working with lists of properties.
 */
interface PropertyListInterface extends ArrayAccess, IteratorAggregate, Countable { }