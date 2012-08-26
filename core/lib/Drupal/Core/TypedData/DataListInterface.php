<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataListInterface.
 */

namespace Drupal\Core\TypedData;
use ArrayAccess;
use IteratorAggregate;
use Countable;

/**
 * Interface for a list of typed data.
 */
interface DataListInterface extends ArrayAccess, IteratorAggregate , Countable { }