<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\ListInterface.
 */

namespace Drupal\Core\TypedData;
use ArrayAccess;
use IteratorAggregate;
use Countable;

/**
 * Interface for a list of typed data.
 */
interface ListInterface extends ArrayAccess, IteratorAggregate , Countable { }